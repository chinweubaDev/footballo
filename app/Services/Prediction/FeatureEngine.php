<?php

namespace App\Services\Prediction;

use App\Services\Prediction\Support\PredictionContext;
use App\Services\Prediction\Support\ProbabilityValidator;

/**
 * Converts raw football data into a deterministic feature vector and computes
 * the data-quality score. This is the single source of truth for feature
 * calculation so the math is never duplicated across models.
 */
class FeatureEngine
{
    /**
     * Recency weights for the five most recent matches (most recent first).
     */
    protected array $formWeights = [1.0, 0.85, 0.70, 0.55, 0.40];

    /**
     * Feature groups that can be neutralised for ablation validation (Phase 1G §36).
     * "standings" is deliberately absent: no standalone standings feature exists
     * in the vector, so there is nothing to remove.
     */
    public const ABLATABLE = ['form', 'h2h', 'team_strength', 'xg'];

    public function build(PredictionContext $context, array $ablated = []): array
    {
        $ablated = array_values(array_intersect($ablated, self::ABLATABLE));

        $homeFormScore = $this->formScore($context->homeForm);
        $awayFormScore = $this->formScore($context->awayForm);

        $homeHomeScore = $this->formScore($context->homeHomeForm);
        $awayAwayScore = $this->formScore($context->awayAwayForm);

        $homePpg = $this->pointsPerGame($context->homeForm);
        $awayPpg = $this->pointsPerGame($context->awayForm);

        $leagueHome = (float) config('prediction.poisson.league_home_goals_default', 1.45);
        $leagueAway = (float) config('prediction.poisson.league_away_goals_default', 1.15);

        $homeGoalAvg = $this->avgGoals($context->homeTeamStats, 'avg_goals_for_home', $context->homeForm, 'for');
        $homeConcAvg = $this->avgGoals($context->homeTeamStats, 'avg_goals_against_home', $context->homeForm, 'against');
        $awayGoalAvg = $this->avgGoals($context->awayTeamStats, 'avg_goals_for_away', $context->awayForm, 'for');
        $awayConcAvg = $this->avgGoals($context->awayTeamStats, 'avg_goals_against_away', $context->awayForm, 'against');

        $homeAttack = $this->strength($homeGoalAvg, $leagueHome);
        $awayAttack = $this->strength($awayGoalAvg, $leagueAway);
        $homeDefense = $this->strength($homeConcAvg, $leagueAway);
        $awayDefense = $this->strength($awayConcAvg, $leagueHome);

        $dataQuality = $this->dataQuality($context);

        $features = [
            'home_form_score' => round($homeFormScore, 2),
            'away_form_score' => round($awayFormScore, 2),
            'home_home_form_score' => round($homeHomeScore, 2),
            'away_away_form_score' => round($awayAwayScore, 2),
            'home_form_ppg' => round($homePpg, 2),
            'away_form_ppg' => round($awayPpg, 2),

            'home_attack_strength' => $homeAttack,
            'away_attack_strength' => $awayAttack,
            'home_defense_strength' => $homeDefense,
            'away_defense_strength' => $awayDefense,

            'home_goal_average' => round($homeGoalAvg, 2),
            'away_goal_average' => round($awayGoalAvg, 2),
            'home_conceded_average' => round($homeConcAvg, 2),
            'away_conceded_average' => round($awayConcAvg, 2),

            'home_xg' => $context->homeTeamStats['xg'] ?? null,
            'away_xg' => $context->awayTeamStats['xg'] ?? null,
            'home_xga' => $context->homeTeamStats['xga'] ?? null,
            'away_xga' => $context->awayTeamStats['xga'] ?? null,

            'home_advantage' => (float) config('prediction.home_advantage.baseline', 0.25),
            'league_home_goals' => $leagueHome,
            'league_away_goals' => $leagueAway,

            'h2h_score' => $this->h2hScore($context->h2h),
            'odds_signal' => ($context->odds['available'] ?? false) ? ($context->odds['home_imp'] ?? null) : null,
            'api_prediction_signal' => ($context->apiPrediction['available'] ?? false) ? ($context->apiPrediction['hp'] ?? null) : null,

            'data_quality' => $dataQuality['score'],
            'data_quality_components' => $dataQuality['components'],
        ];

        return $this->applyAblations($features, $ablated);
    }

    /**
     * Neutralise the contribution of ablated feature groups so a backtest can
     * measure each feature's out-of-sample impact (Phase 1G §36). Neutral
     * values keep the model math well-defined without inventing data:
     *
     *   form          → scores reset to 50 (coin-flip), ppg reset to 0
     *   h2h           → h2h_score reset to 50 (no head-to-head signal)
     *   team_strength → attack/defence strengths reset to 1.0 (league-average)
     *   xg            → expected-goals values removed (null)
     *
     * @param array<string,mixed> $features
     * @param list<string> $ablated
     * @return array<string,mixed>
     */
    protected function applyAblations(array $features, array $ablated): array
    {
        if (empty($ablated)) {
            return $features;
        }

        if (in_array('form', $ablated, true)) {
            $features['home_form_score'] = 50.0;
            $features['away_form_score'] = 50.0;
            $features['home_home_form_score'] = 50.0;
            $features['away_away_form_score'] = 50.0;
            $features['home_form_ppg'] = 0.0;
            $features['away_form_ppg'] = 0.0;
        }

        if (in_array('h2h', $ablated, true)) {
            $features['h2h_score'] = 50.0;
        }

        if (in_array('team_strength', $ablated, true)) {
            $features['home_attack_strength'] = 1.0;
            $features['away_attack_strength'] = 1.0;
            $features['home_defense_strength'] = 1.0;
            $features['away_defense_strength'] = 1.0;
        }

        if (in_array('xg', $ablated, true)) {
            $features['home_xg'] = null;
            $features['away_xg'] = null;
            $features['home_xga'] = null;
            $features['away_xga'] = null;
        }

        return $features;
    }

    /**
     * Normalised 0-100 form score using recency-weighted results and a small
     * goal-difference adjustment.
     *
     * @param list<array{result:string,goals_for:int,goals_against:int,is_home:bool}> $matches
     */
    protected function formScore(array $matches): float
    {
        if (empty($matches)) {
            return 50.0;
        }

        $weightSum = 0.0;
        $pointsSum = 0.0;
        $goalDiff = 0;

        foreach (array_slice($matches, 0, count($this->formWeights)) as $i => $match) {
            $weight = $this->formWeights[$i] ?? end($this->formWeights);
            $points = match ($match['result']) {
                'W' => 3,
                'D' => 1,
                default => 0,
            };

            $weightSum += $weight;
            $pointsSum += $weight * $points;
            $goalDiff += $match['goals_for'] - $match['goals_against'];
        }

        $score = ($pointsSum / max($weightSum, 0.0001)) / 3 * 100;
        $score += ProbabilityValidator::clamp($goalDiff * 1.5, -8.0, 8.0);

        return ProbabilityValidator::clamp($score);
    }

    protected function pointsPerGame(array $matches): float
    {
        if (empty($matches)) {
            return 0.0;
        }

        $points = 0;

        foreach ($matches as $match) {
            $points += match ($match['result']) {
                'W' => 3,
                'D' => 1,
                default => 0,
            };
        }

        return round($points / count($matches), 2);
    }

    /**
     * Prefer team statistics; fall back to recent form averages.
     */
    protected function avgGoals(array $stats, string $statKey, array $form, string $direction): float
    {
        $value = $stats[$statKey] ?? null;

        if (is_numeric($value) && $value > 0) {
            return (float) $value;
        }

        if (empty($form)) {
            return 0.0;
        }

        $total = 0;
        $count = 0;

        foreach ($form as $match) {
            $total += $direction === 'for' ? $match['goals_for'] : $match['goals_against'];
            $count++;
        }

        return $count ? round($total / $count, 2) : 0.0;
    }

    protected function strength(float $average, float $leagueAverage): float
    {
        if ($average <= 0 || $leagueAverage <= 0) {
            return 1.0;
        }

        return ProbabilityValidator::clamp($average / $leagueAverage, 0.5, 2.0);
    }

    protected function h2hScore(array $h2h): float
    {
        if (($h2h['matches'] ?? 0) <= 0) {
            return 50.0;
        }

        $diff = ($h2h['home_win_rate'] ?? 0) - ($h2h['away_win_rate'] ?? 0);

        return ProbabilityValidator::clamp(50 + $diff);
    }

    /**
     * Weighted 0-100 data quality score. Required data (form, team stats)
     * carries the most weight; optional data is not penalised excessively.
     */
    protected function dataQuality(PredictionContext $context): array
    {
        $components = [
            'form' => count($context->homeForm) >= 3 && count($context->awayForm) >= 3,
            'team_stats' => ! empty($context->homeTeamStats) && ! empty($context->awayTeamStats),
            'home_away' => ! empty($context->homeHomeForm) && ! empty($context->awayAwayForm),
            'h2h' => ($context->h2h['matches'] ?? 0) > 0,
            'odds' => (bool) ($context->odds['available'] ?? false),
            'api_prediction' => (bool) ($context->apiPrediction['available'] ?? false),
            'injuries' => (bool) ($context->injuries['fetched'] ?? false),
        ];

        $weights = [
            'form' => 15,
            'team_stats' => 20,
            'home_away' => 15,
            'h2h' => 10,
            'odds' => 20,
            'api_prediction' => 10,
            'injuries' => 10,
        ];

        $score = 0;

        foreach ($components as $key => $present) {
            if ($present) {
                $score += $weights[$key];
            }
        }

        return ['score' => $score, 'components' => $components];
    }
}
