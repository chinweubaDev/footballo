<?php

namespace App\Services\Prediction;

use App\Models\Fixture;
use App\Services\ApiFootballServiceEnhanced;
use App\Services\Prediction\Support\PredictionContext;

/**
 * Gathers all raw data for a fixture ONCE from API-Football and wraps it in a
 * PredictionContext. Models must never issue their own API requests.
 */
class DataCollector
{
    public function __construct(protected ApiFootballServiceEnhanced $api)
    {
    }

    public function collect(Fixture $fixture): PredictionContext
    {
        $homeId = (int) $fixture->home_team_id;
        $awayId = (int) $fixture->away_team_id;
        $leagueId = (int) $fixture->league_id;
        $season = $fixture->season ? (string) $fixture->season : null;

        $homeForm = $this->formList($homeId);
        $awayForm = $this->formList($awayId);

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
            homeTeamStats: $this->teamStats($homeId, $leagueId, $season),
            awayTeamStats: $this->teamStats($awayId, $leagueId, $season),
            h2h: $this->h2h($homeId, $awayId),
            odds: $this->odds((int) $fixture->api_fixture_id),
            apiPrediction: $this->apiPrediction((int) $fixture->api_fixture_id),
            standings: $this->standings($homeId, $awayId, $leagueId, $season),
            injuries: $this->injuries($homeId, $awayId, $leagueId, $season),
        );
    }

    /**
     * @return list<array{result:string,goals_for:int,goals_against:int,is_home:bool}>
     */
    protected function formList(int $teamId, int $count = 10): array
    {
        $data = $this->api->getTeamLastFixtures($teamId, $count);

        if (! $data || empty($data['response'])) {
            return [];
        }

        $list = [];

        foreach ($data['response'] as $match) {
            $status = $match['fixture']['status']['short'] ?? null;

            if (! in_array($status, ['FT', 'AET', 'PEN'], true)) {
                continue;
            }

            $isHome = ((int) ($match['teams']['home']['id'] ?? 0)) === $teamId;
            $gf = (int) ($match['goals'][$isHome ? 'home' : 'away'] ?? 0);
            $ga = (int) ($match['goals'][$isHome ? 'away' : 'home'] ?? 0);

            $result = $gf > $ga ? 'W' : ($gf < $ga ? 'L' : 'D');

            $list[] = [
                'result' => $result,
                'goals_for' => $gf,
                'goals_against' => $ga,
                'is_home' => $isHome,
            ];
        }

        return $list;
    }

    protected function teamStats(int $teamId, int $leagueId, ?string $season): array
    {
        if (! $leagueId || ! $season) {
            return [];
        }

        $data = $this->api->getTeamStatistics($teamId, $leagueId, (int) $season);

        if (! $data || empty($data['response'])) {
            return [];
        }

        $row = $data['response'][0] ?? $data['response'];
        $goals = $row['goals'] ?? [];
        $cleanSheet = $row['clean_sheet'] ?? [];

        return [
            'form' => $row['form'] ?? null,
            'played_total' => (int) ($row['fixtures']['played']['total'] ?? 0),
            'avg_goals_for_home' => $this->float($goals['for']['average']['home'] ?? null),
            'avg_goals_for_away' => $this->float($goals['for']['average']['away'] ?? null),
            'avg_goals_against_home' => $this->float($goals['against']['average']['home'] ?? null),
            'avg_goals_against_away' => $this->float($goals['against']['average']['away'] ?? null),
            'clean_sheet_home' => (int) ($cleanSheet['home'] ?? 0),
            'clean_sheet_away' => (int) ($cleanSheet['away'] ?? 0),
            'bts_total' => (int) ($row['goals']['bts']['total'] ?? 0),
        ];
    }

    protected function h2h(int $homeId, int $awayId): array
    {
        $data = $this->api->getHeadToHead($homeId, $awayId, 10);

        if (! $data || empty($data['response'])) {
            return ['matches' => 0];
        }

        $hw = $aw = $dr = $hg = $ag = $o25 = $bts = 0;

        foreach ($data['response'] as $match) {
            $gh = (int) ($match['goals']['home'] ?? 0);
            $ga = (int) ($match['goals']['away'] ?? 0);
            $isHome = ((int) ($match['teams']['home']['id'] ?? 0)) === $homeId;

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

        $total = count($data['response']);

        return [
            'matches' => $total,
            'home_win_rate' => $total ? round($hw / $total * 100, 1) : 0.0,
            'away_win_rate' => $total ? round($aw / $total * 100, 1) : 0.0,
            'draw_rate' => $total ? round($dr / $total * 100, 1) : 0.0,
            'avg_home_goals' => $total ? round($hg / $total, 2) : 0.0,
            'avg_away_goals' => $total ? round($ag / $total, 2) : 0.0,
            'over25_rate' => $total ? round($o25 / $total * 100, 1) : 0.0,
            'bts_rate' => $total ? round($bts / $total * 100, 1) : 0.0,
        ];
    }

    protected function odds(int $fixtureApiId): array
    {
        $data = $this->api->getOdds($fixtureApiId);

        if (! $data || empty($data['response'])) {
            return ['available' => false];
        }

        $bookmakers = $data['response'][0]['bookmakers'] ?? [];

        if (empty($bookmakers)) {
            return ['available' => false];
        }

        $bets = $bookmakers[0]['bets'] ?? [];

        $ho = $do = $ao = $o25 = $u25 = $o15 = $u15 = $by = $bn = 0.0;

        foreach ($bets as $bet) {
            $betId = $bet['id'] ?? 0;
            $values = $bet['values'] ?? [];

            if ($betId === 1) {
                foreach ($values as $v) {
                    match ($v['value'] ?? '') {
                        'Home' => $ho = $this->float($v['odd']),
                        'Draw' => $do = $this->float($v['odd']),
                        'Away' => $ao = $this->float($v['odd']),
                        default => null,
                    };
                }
            }

            if ($betId === 5) {
                foreach ($values as $v) {
                    $val = $v['value'] ?? '';

                    if (str_contains($val, 'Over 2.5')) {
                        $o25 = $this->float($v['odd']);
                    } elseif (str_contains($val, 'Under 2.5')) {
                        $u25 = $this->float($v['odd']);
                    } elseif (str_contains($val, 'Over 1.5')) {
                        $o15 = $this->float($v['odd']);
                    } elseif (str_contains($val, 'Under 1.5')) {
                        $u15 = $this->float($v['odd']);
                    }
                }
            }

            if ($betId === 8) {
                foreach ($values as $v) {
                    if (($v['value'] ?? '') === 'Yes') {
                        $by = $this->float($v['odd']);
                    } elseif (($v['value'] ?? '') === 'No') {
                        $bn = $this->float($v['odd']);
                    }
                }
            }
        }

        return [
            'available' => true,
            'bookmaker' => $bookmakers[0]['name'] ?? 'Unknown',
            'home_odds' => $ho,
            'draw_odds' => $do,
            'away_odds' => $ao,
            'over25_odds' => $o25,
            'under25_odds' => $u25,
            'over15_odds' => $o15,
            'under15_odds' => $u15,
            'bts_yes' => $by,
            'bts_no' => $bn,
            'home_imp' => $this->implied($ho, $do, $ao),
            'draw_imp' => $this->implied($do, $ho, $ao),
            'away_imp' => $this->implied($ao, $ho, $do),
            'o25_imp' => $o25 > 0 ? round(100 / $o25, 1) : 0.0,
            'o15_imp' => $o15 > 0 ? round(100 / $o15, 1) : 0.0,
            'bts_imp' => $by > 0 ? round(100 / $by, 1) : 0.0,
        ];
    }

    protected function implied(float $o, float $o2, float $o3): float
    {
        if ($o <= 0) {
            return 0.0;
        }

        $r = 1 / $o;
        $total = $r + ($o2 > 0 ? 1 / $o2 : 0) + ($o3 > 0 ? 1 / $o3 : 0);

        return $total > 0 ? round(($r / $total) * 100, 1) : 0.0;
    }

    protected function apiPrediction(int $fixtureApiId): array
    {
        $data = $this->api->getPredictions($fixtureApiId);

        if (! $data || empty($data['response'])) {
            return ['available' => false];
        }

        $p = $data['response'][0]['predictions'] ?? [];

        return [
            'available' => true,
            'hp' => $this->intPercent($p['percent']['home'] ?? null),
            'dp' => $this->intPercent($p['percent']['draw'] ?? null),
            'ap' => $this->intPercent($p['percent']['away'] ?? null),
            'advice' => $p['advice'] ?? null,
            'winner' => $p['winner']['name'] ?? null,
        ];
    }

    protected function standings(int $homeId, int $awayId, int $leagueId, ?string $season): array
    {
        if (! $leagueId || ! $season) {
            return [];
        }

        $data = $this->api->getStandings($leagueId, (int) $season);

        if (! $data || empty($data['response'])) {
            return [];
        }

        $hp = $ap = $hpt = $apt = 0;

        foreach ($data['response'][0]['league']['standings'][0] ?? [] as $team) {
            if (((int) ($team['team']['id'] ?? 0)) === $homeId) {
                $hp = (int) ($team['rank'] ?? 0);
                $hpt = (int) ($team['points'] ?? 0);
            }

            if (((int) ($team['team']['id'] ?? 0)) === $awayId) {
                $ap = (int) ($team['rank'] ?? 0);
                $apt = (int) ($team['points'] ?? 0);
            }
        }

        return [
            'home_position' => $hp,
            'away_position' => $ap,
            'home_points' => $hpt,
            'away_points' => $apt,
            'position_diff' => $ap - $hp,
            'points_diff' => $hpt - $apt,
        ];
    }

    protected function injuries(int $homeId, int $awayId, int $leagueId, ?string $season): array
    {
        $home = $this->api->getInjuriesByTeam($homeId, $season ?? (string) date('Y'));
        $away = $this->api->getInjuriesByTeam($awayId, $season ?? (string) date('Y'));

        $homeMissing = is_array($home) ? count($home['response'] ?? []) : 0;
        $awayMissing = is_array($away) ? count($away['response'] ?? []) : 0;

        return [
            'fetched' => is_array($home) || is_array($away),
            'home_missing' => $homeMissing,
            'away_missing' => $awayMissing,
            'total_missing' => $homeMissing + $awayMissing,
            'has_injuries' => $homeMissing + $awayMissing > 0,
        ];
    }

    protected function float(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    protected function intPercent(mixed $value): float
    {
        if (! is_numeric($value)) {
            return 0.0;
        }

        return round((float) str_replace('%', '', (string) $value), 1);
    }
}
