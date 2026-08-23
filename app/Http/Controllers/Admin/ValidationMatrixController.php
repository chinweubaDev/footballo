<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicationCandidate;
use App\Services\Prediction\Validation\MultiSeasonAnalysisService;
use App\Services\Prediction\Validation\ValidationMatrixService;
use Illuminate\Http\Request;

/**
 * Phase 1G.2 — full League x Market x Model validation matrix dashboard.
 */
class ValidationMatrixController extends Controller
{
    public function __construct(
        protected ValidationMatrixService $matrix,
        protected MultiSeasonAnalysisService $multiSeason,
    ) {
    }

    /**
     * Phase 1P — multi-season validation (backtest data only).
     */
    public function multiSeason(Request $request)
    {
        $model = $request->query('model');

        return view('admin.predictions.validation.multi-season', [
            'inventory' => $this->multiSeason->inventory(),
            'seasons' => $this->multiSeason->seasons(),
            'marketGeneralization' => $this->multiSeason->marketGeneralization($model),
            'leagueGeneralization' => $this->multiSeason->leagueGeneralization($model),
            'leagueMarketPooled' => $this->multiSeason->leagueMarketPooled($model),
            'temporalStability' => $this->multiSeason->temporalStability($model),
            'filter' => $model,
        ]);
    }

    /**
     * /admin/predictions/validation/matrix
     */
    public function matrix(Request $request)
    {
        $season = $request->query('season') ? (int) $request->query('season') : null;
        $model = $request->query('model');
        $market = $request->query('market');
        $threshold = $request->query('threshold') !== null ? (float) $request->query('threshold') : null;

        $report = $this->matrix->matrix($season, $model);

        $rows = $this->flattenCells($report);

        if ($model) {
            $rows = array_values(array_filter($rows, fn ($r) => $r['model'] === $model));
        }

        if ($market) {
            $rows = array_values(array_filter($rows, fn ($r) => $r['market'] === $market));
        }

        if ($threshold !== null) {
            $rows = array_values(array_filter($rows, fn ($r) => ($r['accuracy'] ?? 0) >= $threshold));
        }

        return view('admin.predictions.validation.matrix', [
            'report' => $report,
            'rows' => $rows,
            'leagues' => $this->leagueOptions($report),
            'models' => $report['versions'],
            'markets' => ValidationMatrixService::MARKET_LABELS,
            'filters' => ['season' => $season, 'model' => $model, 'market' => $market, 'threshold' => $threshold],
        ]);
    }

    /**
     * /admin/predictions/validation/ranking
     */
    public function ranking(Request $request)
    {
        $season = $request->query('season') ? (int) $request->query('season') : null;
        $model = $request->query('model');

        $report = $this->matrix->matrix($season, $model);

        $ranking = [];

        foreach ($report['models'] as $version => $m) {
            if ($model && $version !== $model) {
                continue;
            }

            foreach ($m['ranking'] as $row) {
                $ranking[] = $row;
            }
        }

        usort($ranking, function ($a, $b) {
            if (($a['sample_status'] ?? '') !== ($b['sample_status'] ?? '')) {
                $order = ['ADEQUATE' => 0, 'LOW' => 1, 'INSUFFICIENT' => 2];

                return ($order[$a['sample_status']] ?? 9) <=> ($order[$b['sample_status']] ?? 9);
            }

            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        foreach ($ranking as $i => $row) {
            $ranking[$i]['rank'] = $i + 1;
        }

        return view('admin.predictions.validation.ranking', [
            'report' => $report,
            'ranking' => $ranking,
            'models' => $report['versions'],
            'filters' => ['season' => $season, 'model' => $model],
        ]);
    }

    /**
     * /admin/predictions/validation/candidates
     */
    public function candidates(Request $request)
    {
        $season = $request->query('season') ? (int) $request->query('season') : null;
        $model = $request->query('model');

        $report = $this->matrix->matrix($season, $model);

        $candidates = [];

        foreach ($report['models'] as $version => $m) {
            foreach ($m['publication_candidates'] as $candidate) {
                if ($model && $candidate['model'] !== $model) {
                    continue;
                }

                $candidates[] = $candidate;
            }
        }

        return view('admin.predictions.validation.candidates', [
            'report' => $report,
            'candidates' => $candidates,
            'models' => $report['versions'],
            'filters' => ['season' => $season, 'model' => $model],
        ]);
    }

    /**
     * Approve or reject a publication candidate (admin-only, explicit).
     */
    public function decide(Request $request)
    {
        $validated = $request->validate([
            'league_id' => ['required', 'integer'],
            'market_code' => ['required', 'string'],
            'model_version' => ['required', 'string'],
            'status' => ['required', 'in:approved,rejected'],
            'recommended_probability' => ['nullable', 'integer'],
            'recommended_confidence' => ['nullable', 'integer'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $candidate = PublicationCandidate::updateOrCreate(
            [
                'league_id' => $validated['league_id'],
                'market_code' => $validated['market_code'],
                'model_version' => $validated['model_version'],
            ],
            [
                'status' => $validated['status'],
                'recommended_probability' => $validated['recommended_probability'] ?? null,
                'recommended_confidence' => $validated['recommended_confidence'] ?? null,
                'approved_at' => $validated['status'] === 'approved' ? now() : null,
                'approved_by' => $request->user()?->id,
                'metrics' => ['reason' => $validated['reason'] ?? null],
            ],
        );

        $label = ucfirst($validated['status']);

        return back()->with('success', "Candidate marked {$label}.");
    }

    /**
     * @return list<array<string,mixed>>
     */
    protected function flattenCells(array $report): array
    {
        $rows = [];

        foreach ($report['models'] as $version => $model) {
            foreach ($model['leagues'] as $league) {
                foreach ($league['markets'] as $code => $m) {
                    $rows[] = [
                        'league_id' => $league['league_id'],
                        'league' => $league['league_name'],
                        'market' => $code,
                        'market_label' => $m['market_label'],
                        'model' => $version,
                        'season' => $m['season'],
                        'n' => $m['n'],
                        'accuracy' => $m['accuracy'],
                        'coverage' => $m['coverage'],
                        'brier' => $m['brier'],
                        'log_loss' => $m['log_loss'],
                        'avg_probability' => $m['avg_probability'],
                        'avg_confidence' => $m['avg_confidence'],
                        'calibration' => $m['calibration_error'],
                        'sample_status' => $m['sample_status'],
                        'selections' => $m['selections'],
                        'gate' => $m['gate'],
                        'gate_comparison' => $m['gate_comparison'],
                        'confidence_tiers' => $m['confidence_tiers'],
                        'calibration_buckets' => $m['calibration_buckets'],
                    ];
                }
            }
        }

        return $rows;
    }

    /**
     * @return array<int,string>
     */
    protected function leagueOptions(array $report): array
    {
        $options = [];

        foreach ($report['models'] as $model) {
            foreach ($model['leagues'] as $league) {
                $options[$league['league_id']] = $league['league_name'];
            }
        }

        ksort($options);

        return $options;
    }
}
