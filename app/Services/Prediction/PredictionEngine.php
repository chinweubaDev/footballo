<?php

namespace App\Services\Prediction;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Prediction;
use App\Models\PredictionCategory;
use App\Models\PredictionFeature;
use App\Models\PredictionModel;
use App\Services\Prediction\Admin\MarketGate;
use App\Services\Prediction\Admin\PredictionPublicationService;
use App\Services\Prediction\Calibration\ModelConfigurationService;
use App\Services\Prediction\Calibration\ProbabilityCalibrator;
use App\Services\Prediction\Confidence\ConfidenceEngine;
use App\Services\Prediction\Markets\BttsMarket;
use App\Services\Prediction\Markets\CorrectScoreMarket;
use App\Services\Prediction\Markets\DoubleChanceMarket;
use App\Services\Prediction\Markets\DrawMarket;
use App\Services\Prediction\Markets\GoalsMarket;
use App\Services\Prediction\Markets\OneXTwoMarket;
use App\Services\Prediction\Models\EnsemblePredictionModel;
use App\Services\Prediction\Support\EnsembleResult;
use App\Services\Prediction\Support\ModelPrediction;
use App\Services\Prediction\Support\PredictionContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates the statistical prediction pipeline:
 *
 *   DataCollector -> FeatureEngine -> Models -> Ensemble -> Markets -> Confidence -> NO_BET/PUBLISH
 *
 * It contains no market math itself; each responsibility lives in a dedicated
 * service so models and markets can evolve independently.
 */
class PredictionEngine
{
    public function __construct(
        protected DataCollector $collector,
        protected FeatureEngine $features,
        protected EnsemblePredictionModel $ensemble,
        protected ConfidenceEngine $confidence,
        protected MarketGate $gate,
        protected ?ModelConfigurationService $config = null,
        protected ?PredictionPublicationService $publication = null,
    ) {
        $this->config ??= new ModelConfigurationService();
    }

    /**
     * Compute the full prediction result without writing to the database.
     */
    public function predictFixture(Fixture $fixture): array
    {
        return $this->toArray($fixture, $this->compute($fixture));
    }

    /**
     * Compute and persist per-market predictions + a feature snapshot.
     *
     * When $model is given, predictions are attributed to that model version
     * (used by shadow mode). When $statusOverride is 'shadow', bettable
     * predictions are stored as 'shadow' instead of 'published'/'generated'.
     */
    public function generate(Fixture $fixture, ?PredictionModel $model = null, ?string $statusOverride = null): array
    {
        $state = $this->compute($fixture, $model);
        $result = $this->toArray($fixture, $state);

        DB::transaction(function () use ($fixture, $state, $model, $statusOverride) {
            $this->persist($fixture, $state, $model, $statusOverride);
        });

        return $result;
    }

    /**
     * @return array{context:PredictionContext,features:array,ensemble:EnsembleResult,markets:array,version:string,modelId:?int}
     */
    protected function compute(Fixture $fixture, ?PredictionModel $model = null): array
    {
        $model ??= $this->activeModel();

        $context = $this->collector->collect($fixture);
        $features = $this->features->build($context);
        $ensemble = $this->ensemble->predict($context, $features, $this->resolveWeights($model));

        // Shadow models bypass the publication gate so their predictions are
        // recorded (as 'shadow') for later comparison — never published.
        $isShadow = $model !== null && ! (bool) $model->active;

        $markets = $this->evaluateMarkets($fixture, $context, $ensemble, $model, $isShadow);

        return [
            'context' => $context,
            'features' => $features,
            'ensemble' => $ensemble,
            'markets' => $markets,
            'version' => $model?->version ?? $this->activeVersion(),
            'modelId' => $model?->id ?? $this->activeModelId(),
        ];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    protected function evaluateMarkets(Fixture $fixture, PredictionContext $context, EnsembleResult $ensemble, ?PredictionModel $model = null, bool $shadow = false): array
    {
        $league = $fixture->league ?? null;
        $calibrators = $this->calibrators($model);
        $markets = [];

        foreach ($this->enabledCategories() as $category) {
            $market = $this->marketFor($category->code);

            if ($market === null) {
                continue;
            }

            $calculated = $market->calculate($ensemble);
            $selection = $calculated['selection'];
            $rawProbability = (float) $calculated['probability'];
            $calibratedProbability = $rawProbability;
            $calibrationVersion = null;

            // Apply per-market probability calibration when this model has one
            // (v1.1.0 candidate). v1.0.0 has no calibration and is untouched.
            // The raw probability is preserved alongside the calibrated value.
            if (isset($calibrators[$category->code])) {
                $calibratedProbability = $calibrators[$category->code]->predict($rawProbability);
                $calibrationVersion = $this->config->calibrationVersion($model);
            }

            $confidence = $this->confidence->calculate($context, $category->code, $selection, $calibratedProbability, $ensemble);
            $thresholds = $this->thresholds($league, $category);

            // Shadow models are evaluated but NEVER gated out — the gate
            // values are still recorded so the decision is reproducible.
            $status = $shadow
                ? 'generated'
                : $this->decideStatus($calibratedProbability, $confidence['score'], $ensemble->dataQuality, $thresholds);

            // Publication gate: disabled leagues (or leagues with prediction
            // disabled) never publish, even if the statistical thresholds pass.
            if (! $shadow && $league && (! $league->enabled || ! $league->prediction_enabled)) {
                $status = 'no_bet';
            }

            $markets[$category->code] = [
                'category' => $category->name,
                'slug' => $category->slug,
                'selection' => $selection,
                'probability' => $calibratedProbability,
                'raw_probability' => $rawProbability,
                'calibration_version' => $calibrationVersion,
                'gate' => $thresholds,
                'confidence' => $confidence['score'],
                'confidence_level' => $confidence['level'],
                'confidence_factors' => $confidence['factors'],
                'status' => $status,
            ];

            if (isset($calculated['scores'])) {
                $markets[$category->code]['scores'] = $calculated['scores'];
            }
        }

        return $markets;
    }

    protected function decideStatus(float $probability, int $confidence, int $dataQuality, array $thresholds): string
    {
        $decision = $this->gate->decide(
            $probability,
            $confidence,
            $dataQuality,
            $thresholds['min_probability'],
            $thresholds['min_confidence'],
            $thresholds['min_data_quality'],
        );

        if ($decision === MarketGate::NO_BET) {
            return 'no_bet';
        }

        return $thresholds['auto_publish'] ? 'published' : 'generated';
    }

    /**
     * Threshold precedence (Phase 1I): league+market > market > league > global.
     * When the publication service is available it is the single source of
     * truth; otherwise fall back to the market/league/global chain.
     */
    protected function thresholds(?League $league, PredictionCategory $category): array
    {
        if ($this->publication !== null) {
            $gate = $this->publication->resolveGate($league, $category);

            return [
                'min_confidence' => $gate['min_confidence'],
                'min_probability' => $gate['min_probability'],
                'min_data_quality' => $gate['min_data_quality'],
                'auto_publish' => $league?->auto_publish ?? (bool) config('prediction.auto_publish', true),
                'enabled' => $gate['enabled'],
                'source' => $gate['source'],
            ];
        }

        $minConfidence = $category->min_confidence
            ?? $league?->prediction_min_confidence
            ?? config('prediction.min_confidence', 75);

        $minProbability = $category->min_probability
            ?? $league?->prediction_min_probability
            ?? config('prediction.no_bet.min_probability', 70);

        return [
            'min_confidence' => (int) $minConfidence,
            'min_probability' => (int) $minProbability,
            'min_data_quality' => (int) config('prediction.no_bet.min_data_quality', 65),
            'auto_publish' => $league?->auto_publish ?? (bool) config('prediction.auto_publish', true),
            'enabled' => true,
            'source' => 'market',
        ];
    }

    protected function marketFor(string $code): object|null
    {
        return match ($code) {
            '1x2' => new OneXTwoMarket(),
            'draw' => new DrawMarket(),
            'double_chance' => new DoubleChanceMarket(),
            'over_1_5' => new GoalsMarket('over_1_5', 1.5),
            'over_2_5' => new GoalsMarket('over_2_5', 2.5),
            'btts' => new BttsMarket(),
            'correct_score' => new CorrectScoreMarket(),
            default => null,
        };
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int,PredictionCategory>
     */
    protected function enabledCategories()
    {
        return PredictionCategory::enabled()->orderBy('sort_order')->get();
    }

    protected function activeVersion(): string
    {
        return $this->activeModel()?->version ?? config('prediction.model_version', 'v1.0.0');
    }

    protected function activeModelId(): ?int
    {
        return $this->activeModel()?->id;
    }

    protected function activeModel(): ?PredictionModel
    {
        return PredictionModel::query()->where('active', true)->first();
    }

    protected function resolveWeights(?PredictionModel $model = null): array
    {
        return $this->config->resolveWeights($model ?? $this->activeModel());
    }

    /**
     * Build per-market probability calibrators from a model's configuration.
     * Models without calibration (e.g. v1.0.0) yield an empty map.
     *
     * @return array<string,ProbabilityCalibrator>
     */
    protected function calibrators(?PredictionModel $model): array
    {
        return $this->config->calibrators($model);
    }

    /**
     * Persist per-market prediction rows plus a single feature snapshot.
     */
    protected function persist(Fixture $fixture, array $state, ?PredictionModel $model = null, ?string $statusOverride = null): void
    {
        $version = $state['version'];
        $modelId = $state['modelId'];
        $features = $state['features'];
        $ensemble = $state['ensemble'];
        $context = $state['context'];

        foreach ($state['markets'] as $code => $market) {
            $existing = Prediction::where('fixture_id', $fixture->id)
                ->where('market_code', $code)
                ->where('model_version', $version)
                ->first();

            if ($existing && ($existing->locked_at !== null || $existing->admin_selection !== null)) {
                // Locked or admin-overridden predictions are never overwritten
                // by automatic regeneration (Phase 1C generation safety).
                continue;
            }

            $status = $market['status'];

            if ($statusOverride === 'shadow' && in_array($status, ['published', 'generated'], true)) {
                // Shadow mode: generate but never publish publicly.
                $status = 'shadow';
            }

            Prediction::updateOrCreate(
                [
                    'fixture_id' => $fixture->id,
                    'market_code' => $code,
                    'model_version' => $version,
                ],
                [
                    'category' => $this->legacyCategory($code),
                    'tip' => $this->legacyTip($code, $market['selection']),
                    'confidence' => $market['confidence'],
                    'odds' => $this->bestOdds($context, $code, $market['selection']),
                    'analysis' => $this->explanation($fixture, $code, $market, $features),
                    'status' => $status,
                    'selection' => $market['selection'],
                    'probability' => $market['probability'],
                    'raw_probability' => $market['raw_probability'] ?? $market['probability'],
                    'calibrated_probability' => $market['probability'],
                    'calibration_version' => $market['calibration_version'] ?? null,
                    'gate_probability' => $market['gate']['min_probability'] ?? null,
                    'gate_confidence' => $market['gate']['min_confidence'] ?? null,
                    'configuration_version' => $this->publication?->configurationVersion(),
                    'model_id' => $modelId,
                    'league_id' => $fixture->league_id,
                    'data_quality_score' => $ensemble->dataQuality,
                    'data_quality_flags' => $this->dataQualityFlags($context),
                    'prediction_generated_at' => now(),
                    'feature_data_timestamp' => now(),
                    'explanation' => $this->explanation($fixture, $code, $market, $features),
                    'explanation_status' => 'generated',
                    'published_at' => $status === 'published' ? now() : null,
                    'prediction_data' => isset($market['scores']) ? ['scores' => $market['scores']] : null,
                ]
            );
        }

        PredictionFeature::updateOrCreate(
            [
                'fixture_id' => $fixture->id,
                'model_version' => $version,
            ],
            [
                'prediction_id' => null,
                'features' => $this->featureSnapshot($features, $ensemble),
            ]
        );
    }

    /**
     * Store the feature vector plus ensemble diagnostics (model agreement and
     * per-model signals) for transparency and future auditing.
     */
    protected function featureSnapshot(array $features, EnsembleResult $ensemble): array
    {
        return array_merge($features, [
            'model_agreement' => $ensemble->modelAgreement,
            'expected_home_goals' => $ensemble->expectedHomeGoals,
            'expected_away_goals' => $ensemble->expectedAwayGoals,
            'model_signals' => array_map(function (ModelPrediction $model) {
                return [
                    'name' => $model->name,
                    'home' => $model->homeProbability,
                    'draw' => $model->drawProbability,
                    'away' => $model->awayProbability,
                    'available' => $model->available,
                ];
            }, $ensemble->modelPredictions),
        ]);
    }

    /**
     * Structured missing-feature flags. Missing data is recorded as MISSING —
     * it never inflates confidence or data quality (Phase 1J §24).
     *
     * @return array<string,bool>
     */
    protected function dataQualityFlags(PredictionContext $context): array
    {
        return [
            'odds_missing' => ! (bool) ($context->odds['available'] ?? false),
            'api_ai_missing' => ! (bool) ($context->apiPrediction['available'] ?? false),
            'injuries_missing' => ! (bool) ($context->injuries['fetched'] ?? false),
            'lineup_missing' => true, // lineups are not collected by the current pipeline
        ];
    }

    protected function bestOdds(PredictionContext $context, string $code, string $selection): ?float
    {
        $odds = $context->odds;

        if (! ($odds['available'] ?? false)) {
            return null;
        }

        return match ($code) {
            '1x2' => $this->pickOdds($odds, ['home' => 'home_odds', 'draw' => 'draw_odds', 'away' => 'away_odds'], $selection),
            'draw' => $odds['draw_odds'] ?: null,
            'over_1_5' => str_starts_with($selection, 'over') ? ($odds['over15_odds'] ?: null) : ($odds['under15_odds'] ?: null),
            'over_2_5' => str_starts_with($selection, 'over') ? ($odds['over25_odds'] ?: null) : ($odds['under25_odds'] ?: null),
            'btts' => $selection === 'yes' ? ($odds['bts_yes'] ?: null) : ($odds['bts_no'] ?: null),
            default => null,
        };
    }

    protected function pickOdds(array $odds, array $map, string $selection): ?float
    {
        $key = $map[$selection] ?? null;

        if ($key === null || empty($odds[$key])) {
            return null;
        }

        return (float) $odds[$key];
    }

    protected function legacyCategory(string $code): string
    {
        return match ($code) {
            '1x2' => '1X2',
            'draw' => 'Draw',
            'double_chance' => 'Double Chance',
            'over_1_5' => 'Over 1.5',
            'over_2_5' => 'Over 2.5',
            'btts' => 'Both Teams to Score',
            'correct_score' => 'Correct Score',
            default => $code,
        };
    }

    protected function legacyTip(string $code, string $selection): string
    {
        return match ($code) {
            '1x2' => match ($selection) {
                'home' => 'Home Win (1)',
                'draw' => 'Draw (X)',
                default => 'Away Win (2)',
            },
            'draw' => 'Draw (X)',
            'double_chance' => match ($selection) {
                '1x' => 'Home or Draw (1X)',
                'x2' => 'Draw or Away (X2)',
                default => 'Home or Away (12)',
            },
            'over_1_5' => $selection === 'over_1_5' ? 'Over 1.5' : 'Under 1.5',
            'over_2_5' => $selection === 'over_2_5' ? 'Over 2.5' : 'Under 2.5',
            'btts' => $selection === 'yes' ? 'GG (Both Teams Score)' : 'NG (No BTTS)',
            'correct_score' => 'Correct Score '.$selection,
            default => $selection,
        };
    }

    protected function explanation(Fixture $fixture, string $code, array $market, array $features): string
    {
        $home = $fixture->home_team;
        $away = $fixture->away_team;

        $formLine = "{$home} form score {$features['home_form_score']} vs {$away} {$features['away_form_score']}";

        return match ($code) {
            '1x2', 'draw' => "The model favors this outcome based on {$formLine}, "
                ."attacking strengths {$features['home_attack_strength']} vs {$features['away_attack_strength']}, "
                .'and an expected-goals estimate of '
                .sprintf('%.2f-%.2f', $features['league_home_goals'] * $features['home_attack_strength'] * $features['away_defense_strength'], $features['league_away_goals'] * $features['away_attack_strength'] * $features['home_defense_strength']).'.',
            'over_1_5', 'over_2_5' => "The goal model estimates an expected total of "
                .sprintf('%.2f', $features['league_home_goals'] * $features['home_attack_strength'] * $features['away_defense_strength'] + $features['league_away_goals'] * $features['away_attack_strength'] * $features['home_defense_strength'])
                ." goals, giving a {$market['probability']}% chance for this line.",
            'btts' => "Both teams' scoring rates (attack strengths {$features['home_attack_strength']} vs {$features['away_attack_strength']}) "
                ."produce a {$market['probability']}% chance of both teams scoring.",
            'double_chance' => "This double-chance selection combines the model's strongest outcomes with a combined probability of {$market['probability']}%.",
            default => "This market is derived from the Poisson score distribution (top score {$market['selection']} at {$market['probability']}%).",
        };
    }

    /**
     * Structured output (matches the documented prediction result shape).
     */
    protected function toArray(Fixture $fixture, array $state): array
    {
        $ensemble = $state['ensemble'];

        return [
            'fixture_id' => $fixture->api_fixture_id,
            'model_version' => $state['version'],
            'data_quality' => $ensemble->dataQuality,
            'expected_goals' => [
                'home' => $ensemble->expectedHomeGoals,
                'away' => $ensemble->expectedAwayGoals,
            ],
            'markets' => $state['markets'],
        ];
    }
}
