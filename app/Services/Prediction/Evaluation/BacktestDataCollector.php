<?php

namespace App\Services\Prediction\Evaluation;

use App\Models\Fixture;
use App\Services\Prediction\Support\PredictionContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds a PredictionContext for a HISTORICAL fixture using only data that
 * would have been available before kickoff.
 *
 * This is the data-leakage guard for backtesting. It never calls the live
 * API-Football client (which would return CURRENT form/odds), and it only
 * reads fixtures whose match_date is strictly before the fixture being
 * predicted (walk-forward evaluation).
 *
 * For performance, the completed fixtures for the current scope are loaded
 * into memory once via warm() and every per-fixture computation happens in
 * memory. The output feeds the SAME FeatureEngine / models / markets used by
 * the live engine, so backtest predictions are produced by identical math —
 * but with leak-free historical inputs.
 */
class BacktestDataCollector
{
    /**
     * All completed fixtures for the current scope, sorted by match_date.
     *
     * @var Collection<int,Fixture>|null
     */
    protected ?Collection $dataset = null;

    /**
     * Preload the walk-forward dataset once for a league/season scope.
     */
    public function warm(?int $leagueId, ?int $season): void
    {
        $this->dataset = Fixture::query()
            ->whereIn('status', config('evaluation.terminal_statuses', ['FT', 'AET', 'PEN']))
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->when($leagueId > 0, fn ($q) => $q->where('league_id', $leagueId))
            ->when($season !== null && $season !== '', fn ($q) => $q->where('season', (int) $season))
            ->orderBy('match_date')
            ->get(['id', 'league_id', 'season', 'home_team_id', 'away_team_id', 'home_goals', 'away_goals', 'match_date']);
    }

    /**
     * Clear the cached dataset.
     */
    public function reset(): void
    {
        $this->dataset = null;
    }

    /**
     * Build the pre-match context for a historical fixture.
     */
    public function collect(Fixture $fixture): PredictionContext
    {
        // Lazily warm the dataset if the caller did not.
        if ($this->dataset === null) {
            $this->warm((int) $fixture->league_id, $fixture->season ? (int) $fixture->season : null);
        }

        $before = $fixture->match_date;
        $homeId = (int) $fixture->home_team_id;
        $awayId = (int) $fixture->away_team_id;
        $leagueId = (int) $fixture->league_id;
        $season = $fixture->season ? (string) $fixture->season : null;

        $formCount = (int) config('evaluation.walk_forward.form_matches', 10);

        $homeForm = $this->formList($homeId, $before, $formCount);
        $awayForm = $this->formList($awayId, $before, $formCount);

        return new PredictionContext(
            fixture: $fixture,
            homeTeamId: $homeId,
            awayTeamId: $awayId,
            leagueId: $leagueId,
            season: $season,
            homeForm: $homeForm,
            awayForm: $awayForm,
            homeHomeForm: array_values(array_filter($homeForm, fn ($m) => $m['is_home'])),
            awayAwayForm: array_values(array_filter($awayForm, fn ($m) => ! $m['is_home'])),
            homeTeamStats: $this->teamStats($homeId, $leagueId, $season, $before),
            awayTeamStats: $this->teamStats($awayId, $leagueId, $season, $before),
            h2h: $this->h2h($homeId, $awayId, $before),
            // Historical odds/API-AI predictions/injuries are not stored in this
            // database, so these models report themselves as unavailable rather
            // than inventing values (data leakage protection).
            odds: ['available' => false],
            apiPrediction: ['available' => false],
            standings: $this->standings($homeId, $awayId, $leagueId, $season, $before),
            injuries: ['fetched' => false, 'has_injuries' => false],
        );
    }

    /**
     * The team's most recent completed matches strictly before $before.
     *
     * @return list<array{result:string,goals_for:int,goals_against:int,is_home:bool}>
     */
    protected function formList(int $teamId, ?Carbon $before, int $count): array
    {
        if ($teamId <= 0 || ! $before || $this->dataset === null) {
            return [];
        }

        return $this->dataset
            ->filter(fn (Fixture $m) => $m->match_date
                && $m->match_date->lt($before)
                && ((int) $m->home_team_id === $teamId || (int) $m->away_team_id === $teamId))
            ->sortByDesc(fn (Fixture $m) => $m->match_date)
            ->take($count)
            ->map(fn (Fixture $m) => $this->fromTeamPerspective($m, $teamId))
            ->values()
            ->all();
    }

    /**
     * League-and-season scoped aggregate stats for a team, computed from
     * stored history.
     *
     * @return array<string,mixed>
     */
    protected function teamStats(int $teamId, int $leagueId, ?string $season, ?Carbon $before): array
    {
        if ($teamId <= 0 || ! $before || $this->dataset === null) {
            return [];
        }

        $matches = $this->dataset->filter(fn (Fixture $m) => $m->match_date
            && $m->match_date->lt($before)
            && ((int) $m->home_team_id === $teamId || (int) $m->away_team_id === $teamId)
            && ($leagueId <= 0 || (int) $m->league_id === $leagueId)
            && ($season === null || $season === '' || (string) $m->season === $season));

        if ($matches->isEmpty()) {
            return [];
        }

        $gfHome = $gfAway = $gaHome = $gaAway = 0;
        $nHome = $nAway = 0;
        $cleanSheetHome = $cleanSheetAway = 0;
        $bts = 0;

        foreach ($matches as $m) {
            $isHome = (int) $m->home_team_id === $teamId;
            $gf = $isHome ? (int) $m->home_goals : (int) $m->away_goals;
            $ga = $isHome ? (int) $m->away_goals : (int) $m->home_goals;

            if ($isHome) {
                $nHome++;
                $gfHome += $gf;
                $gaHome += $ga;

                if ($ga === 0) {
                    $cleanSheetHome++;
                }
            } else {
                $nAway++;
                $gfAway += $gf;
                $gaAway += $ga;

                if ($ga === 0) {
                    $cleanSheetAway++;
                }
            }

            if ($gf > 0 && $ga > 0) {
                $bts++;
            }
        }

        return [
            'form' => null,
            'played_total' => $matches->count(),
            'avg_goals_for_home' => $nHome ? round($gfHome / $nHome, 2) : 0.0,
            'avg_goals_for_away' => $nAway ? round($gfAway / $nAway, 2) : 0.0,
            'avg_goals_against_home' => $nHome ? round($gaHome / $nHome, 2) : 0.0,
            'avg_goals_against_away' => $nAway ? round($gaAway / $nAway, 2) : 0.0,
            'clean_sheet_home' => $cleanSheetHome,
            'clean_sheet_away' => $cleanSheetAway,
            'bts_total' => $bts,
        ];
    }

    /**
     * Head-to-head between the two teams before kickoff.
     *
     * @return array<string,mixed>
     */
    protected function h2h(int $homeId, int $awayId, ?Carbon $before): array
    {
        if ($homeId <= 0 || $awayId <= 0 || ! $before || $this->dataset === null) {
            return ['matches' => 0];
        }

        $matches = $this->dataset
            ->filter(fn (Fixture $m) => $m->match_date
                && $m->match_date->lt($before)
                && (((int) $m->home_team_id === $homeId && (int) $m->away_team_id === $awayId)
                    || ((int) $m->home_team_id === $awayId && (int) $m->away_team_id === $homeId)))
            ->sortByDesc(fn (Fixture $m) => $m->match_date)
            ->take(10);

        if ($matches->isEmpty()) {
            return ['matches' => 0];
        }

        $hw = $aw = $dr = $hg = $ag = $o25 = $bts = 0;

        foreach ($matches as $m) {
            $gh = (int) $m->home_goals;
            $ga = (int) $m->away_goals;
            $isHome = (int) $m->home_team_id === $homeId;

            $gf = $isHome ? $gh : $ga;
            $ga2 = $isHome ? $ga : $gh;

            $hg += $gf;
            $ag += $ga2;

            if ($gf > $ga2) {
                $hw++;
            } elseif ($gf < $ga2) {
                $aw++;
            } else {
                $dr++;
            }

            if ($gh + $ga > 2.5) {
                $o25++;
            }

            if ($gh > 0 && $ga > 0) {
                $bts++;
            }
        }

        $total = $matches->count();

        return [
            'matches' => $total,
            'home_win_rate' => round($hw / $total * 100, 1),
            'away_win_rate' => round($aw / $total * 100, 1),
            'draw_rate' => round($dr / $total * 100, 1),
            'avg_home_goals' => round($hg / $total, 2),
            'avg_away_goals' => round($ag / $total, 2),
            'over25_rate' => round($o25 / $total * 100, 1),
            'bts_rate' => round($bts / $total * 100, 1),
        ];
    }

    /**
     * Walk-forward standings: rank/points derived only from prior results in
     * the same league+season.
     *
     * @return array<string,mixed>
     */
    protected function standings(int $homeId, int $awayId, int $leagueId, ?string $season, ?Carbon $before): array
    {
        if ($leagueId <= 0 || ! $before || $this->dataset === null) {
            return [];
        }

        $table = [];

        foreach ($this->dataset as $m) {
            if (! $m->match_date || ! $m->match_date->lt($before)) {
                continue;
            }

            if ((int) $m->league_id !== $leagueId) {
                continue;
            }

            if ($season !== null && $season !== '' && (string) $m->season !== $season) {
                continue;
            }

            $this->applyStandingsRow($table, (int) $m->home_team_id, (int) $m->home_goals, (int) $m->away_goals);
            $this->applyStandingsRow($table, (int) $m->away_team_id, (int) $m->away_goals, (int) $m->home_goals);
        }

        uasort($table, fn ($a, $b) => $b['points'] <=> $a['points']);

        $rank = 0;

        foreach ($table as $teamId => &$row) {
            $rank++;
            $row['rank'] = $rank;
        }
        unset($row);

        $homeRank = $table[$homeId]['rank'] ?? 0;
        $awayRank = $table[$awayId]['rank'] ?? 0;

        return [
            'home_position' => $homeRank,
            'away_position' => $awayRank,
            'home_points' => $table[$homeId]['points'] ?? 0,
            'away_points' => $table[$awayId]['points'] ?? 0,
            'position_diff' => $awayRank - $homeRank,
            'points_diff' => ($table[$homeId]['points'] ?? 0) - ($table[$awayId]['points'] ?? 0),
        ];
    }

    /**
     * @param array<int,array{points:int,wins:int,draws:int,losses:int,gf:int,ga:int}> $table
     */
    protected function applyStandingsRow(array &$table, int $teamId, int $gf, int $ga): void
    {
        if ($teamId <= 0) {
            return;
        }

        $row = $table[$teamId] ?? ['points' => 0, 'wins' => 0, 'draws' => 0, 'losses' => 0, 'gf' => 0, 'ga' => 0];

        $row['gf'] += $gf;
        $row['ga'] += $ga;

        if ($gf > $ga) {
            $row['wins']++;
            $row['points'] += 3;
        } elseif ($gf === $ga) {
            $row['draws']++;
            $row['points'] += 1;
        } else {
            $row['losses']++;
        }

        $table[$teamId] = $row;
    }

    /**
     * @return array{result:string,goals_for:int,goals_against:int,is_home:bool}
     */
    protected function fromTeamPerspective(Fixture $match, int $teamId): array
    {
        $isHome = (int) $match->home_team_id === $teamId;
        $gf = $isHome ? (int) $match->home_goals : (int) $match->away_goals;
        $ga = $isHome ? (int) $match->away_goals : (int) $match->home_goals;

        $result = $gf > $ga ? 'W' : ($gf < $ga ? 'L' : 'D');

        return [
            'result' => $result,
            'goals_for' => $gf,
            'goals_against' => $ga,
            'is_home' => $isHome,
        ];
    }
}
