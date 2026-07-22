<?php

namespace App\Services;

use App\Models\Fixture;

/**
 * Professional Prediction Engine v3
 * 
 * Uses: Market odds (bet IDs 1,5,8) + API AI predictions + full stats + H2H + standings
 * Confidence: odds-implied probability blended with AI and stats
 */
class PredictionEngine
{
    protected ApiFootballServiceEnhanced $api;

    public function __construct(ApiFootballServiceEnhanced $api)
    {
        $this->api = $api;
    }

    public function predictFixture(Fixture $fixture): array
    {
        $hid = $fixture->home_team_id;
        $aid = $fixture->away_team_id;
        $lid = $fixture->league_id;
        $sid = $fixture->season ?? date('Y');
        $fid = $fixture->api_fixture_id;

        // Fetch all data in parallel (logical, not async)
        $odds    = $this->fetchOdds($fid);           // Bet IDs 1,5,8
        $apiPred = $this->fetchApiPrediction($fid);  // API-Football AI
        $h2h     = $this->analyzeH2H($hid, $aid);
        $hf      = $this->analyzeForm($hid);
        $af      = $this->analyzeForm($aid);
        $st      = $this->analyzeStandings($hid, $aid, $lid, $sid);
        $hs      = $this->getTeamStats($hid, $lid, $sid);
        $as      = $this->getTeamStats($aid, $lid, $sid);

        $s = $this->blend($odds, $apiPred, $h2h, $hf, $af, $hs, $as, $st);

        return [
            '1x2'           => $this->p1X2($s, $odds),
            'double_chance' => $this->pDC($s),
            'bts'           => $this->pBTS($s, $odds),
            'over15'        => $this->pOver($s, 1.5, $odds),
            'over25'        => $this->pOver($s, 2.5, $odds),
            'over35'        => $this->pOver($s, 3.5, $odds),
            'draw'          => $this->pDraw($s),
            'correct_score' => $this->pCS($s),
            'half_time'     => $this->pHT($s),
            'xg_home'       => round($s['hxg'], 1),
            'xg_away'       => round($s['axg'], 1),
            'analysis'      => $this->analysis($fixture, $s, $odds, $h2h, $st, $apiPred),
        ];
    }

    // ═══════════════════════════════════════════════
    //  ODDS — proper bet IDs: 1, 5, 8
    // ═══════════════════════════════════════════════

    protected function fetchOdds(int $fixtureId): array
    {
        $d = $this->api->getOdds($fixtureId);
        if (!$d || empty($d['response'])) return $this->emptyOdds();

        $r = $d['response'][0];
        $bms = $r['bookmakers'] ?? [];
        if (empty($bms)) return $this->emptyOdds();

        // Use first bookmaker with Match Winner odds
        $bm = $bms[0];
        $bets = $bm['bets'] ?? [];

        $ho = $do = $ao = 0;
        $o25o = $u25o = $o15o = $u15o = 0;
        $by = $bn = 0;

        foreach ($bets as $bet) {
            $bid = $bet['id'] ?? 0;
            $vals = $bet['values'] ?? [];

            if ($bid === 1) { // Match Winner
                foreach ($vals as $v) {
                    if ($v['value'] === 'Home') $ho = floatval($v['odd']);
                    if ($v['value'] === 'Draw') $do = floatval($v['odd']);
                    if ($v['value'] === 'Away') $ao = floatval($v['odd']);
                }
            }
            if ($bid === 5) { // Goals Over/Under
                foreach ($vals as $v) {
                    $val = $v['value'] ?? '';
                    if (str_contains($val, 'Over 2.5')) $o25o = floatval($v['odd']);
                    if (str_contains($val, 'Under 2.5')) $u25o = floatval($v['odd']);
                    if (str_contains($val, 'Over 1.5')) $o15o = floatval($v['odd']);
                    if (str_contains($val, 'Under 1.5')) $u15o = floatval($v['odd']);
                }
            }
            if ($bid === 8) { // Both Teams Score
                foreach ($vals as $v) {
                    if ($v['value'] === 'Yes') $by = floatval($v['odd']);
                    if ($v['value'] === 'No') $bn = floatval($v['odd']);
                }
            }
        }

        return [
            'bookmaker'    => $bm['name'] ?? 'Unknown',
            'home_odds'    => $ho, 'draw_odds' => $do, 'away_odds' => $ao,
            'over25_odds'  => $o25o, 'under25_odds' => $u25o,
            'over15_odds'  => $o15o, 'under15_odds' => $u15o,
            'bts_yes'      => $by, 'bts_no' => $bn,
            'home_imp'     => $this->implied($ho, $do, $ao),
            'draw_imp'     => $this->implied($do, $ho, $ao),
            'away_imp'     => $this->implied($ao, $ho, $do),
            'o25_imp'      => $o25o > 0 ? round(100 / $o25o) : 0,
            'o15_imp'      => $o15o > 0 ? round(100 / $o15o) : 0,
            'bts_imp'      => $by > 0 ? round(100 / $by) : 0,
        ];
    }

    protected function implied(float $o, float $o2, float $o3): float
    {
        if ($o <= 0) return 0;
        $r = 1 / $o;
        $t = $r + ($o2 > 0 ? 1 / $o2 : 0) + ($o3 > 0 ? 1 / $o3 : 0);
        return round(($r / $t) * 100, 1);
    }

    protected function emptyOdds(): array
    {
        return ['bookmaker' => 'N/A', 'home_odds' => 0, 'draw_odds' => 0, 'away_odds' => 0,
            'over25_odds' => 0, 'under25_odds' => 0, 'over15_odds' => 0, 'under15_odds' => 0,
            'bts_yes' => 0, 'bts_no' => 0,
            'home_imp' => 0, 'draw_imp' => 0, 'away_imp' => 0,
            'o25_imp' => 0, 'o15_imp' => 0, 'bts_imp' => 0];
    }

    // ═══════════════════════════════════════════════
    //  API-FOOTBALL AI PREDICTIONS
    // ═══════════════════════════════════════════════

    protected function fetchApiPrediction(int $fid): array
    {
        $d = $this->api->getPredictions($fid);
        if (!$d || empty($d['response'])) return ['hp' => 0, 'dp' => 0, 'ap' => 0, 'advice' => '', 'winner' => null];
        $p = $d['response'][0]['predictions'] ?? [];
        return [
            'hp'      => intval(str_replace('%', '', $p['percent']['home'] ?? '0')),
            'dp'      => intval(str_replace('%', '', $p['percent']['draw'] ?? '0')),
            'ap'      => intval(str_replace('%', '', $p['percent']['away'] ?? '0')),
            'advice'  => $p['advice'] ?? '',
            'winner'  => $p['winner']['name'] ?? null,
            'comment' => $p['winner']['comment'] ?? '',
        ];
    }

    // ═══════════════════════════════════════════════
    //  SIGNAL BLENDING
    // ═══════════════════════════════════════════════

    protected function blend(array $o, array $a, array $h2h, array $hf, array $af, array $hs, array $as, array $st): array
    {
        // 40% odds, 30% AI, 15% stats, 10% form, 5% H2H
        $ho = $o['home_imp'] ?: 42; $do = $o['draw_imp'] ?: 26; $ao = $o['away_imp'] ?: 32;
        $ha = $a['hp'] ?: 42; $da = $a['dp'] ?: 26; $aa = $a['ap'] ?: 32;
        $hfS = ($hf['ppg'] / 3) * 100; $afS = ($af['ppg'] / 3) * 100;

        $home = $ho * 0.40 + $ha * 0.30 + $hfS * 0.10 + ($h2h['home_win_rate'] ?? 40) * 0.05 + max(0, ($st['position_diff'] ?? 0)) * 0.3 + 5;
        $draw = $do * 0.40 + $da * 0.30 + 25 * 0.10 + ($h2h['draw_rate'] ?? 25) * 0.05 + 10;
        $away = $ao * 0.40 + $aa * 0.30 + $afS * 0.10 + ($h2h['away_win_rate'] ?? 30) * 0.05 + max(0, -($st['position_diff'] ?? 0)) * 0.3 + 5;

        $t = $home + $draw + $away;
        $home = ($home / $t) * 100; $draw = ($draw / $t) * 100; $away = ($away / $t) * 100;

        // xG from season stats
        $hg = floatval($hs['avg_goals_for_home'] ?: 1.5);
        $ag = floatval($as['avg_goals_for_away'] ?: 1.0);
        $hc = floatval($hs['avg_goals_against_home'] ?: 1.0);
        $ac = floatval($as['avg_goals_against_away'] ?: 1.5);
        $hxg = ($hg + $ac) / 2;
        $axg = ($ag + $hc) / 2;

        // BTTS blend
        $bp = ($o['bts_imp'] ?: 50) * 0.55 + ((($hs['bts_total'] / max($hs['played_total'], 1)) * 50) + (($as['bts_total'] / max($as['played_total'], 1)) * 50)) * 0.45;

        return [
            'home_win_prob' => round($home, 1), 'draw_prob' => round($draw, 1), 'away_win_prob' => round($away, 1),
            'hxg' => round($hxg, 2), 'axg' => round($axg, 2), 'txg' => round($hxg + $axg, 2),
            'bts_prob' => round($bp, 1),
            'o15_prob' => round(min(($hxg + $axg) / 1.5 * 100, 97), 1),
            'o25_prob' => round(min(($hxg + $axg) / 2.5 * 100, 95), 1),
            'o35_prob' => round(min(($hxg + $axg) / 3.5 * 100, 90), 1),
            'odds' => $o, 'api' => $a, 'h2h' => $h2h, 'home_form' => $hf, 'away_form' => $af, 'standings' => $st,
        ];
    }

    // ═══════════════════════════════════════════════
    //  PREDICTION OUTPUTS
    // ═══════════════════════════════════════════════

    protected function p1X2(array $s, array $odds): array
    {
        $p = ['home' => $s['home_win_prob'], 'draw' => $s['draw_prob'], 'away' => $s['away_win_prob']];
        arsort($p);
        $pick = array_key_first($p);
        $conf = round(reset($p));
        if ($pick === 'home' && $odds['home_imp'] > 0) $conf = max($conf, (int) round($odds['home_imp']));
        if ($pick === 'away' && $odds['away_imp'] > 0) $conf = max($conf, (int) round($odds['away_imp']));
        if ($pick === 'draw' && $odds['draw_imp'] > 0) $conf = max($conf, (int) round($odds['draw_imp']));
        return [
            'pick'         => $pick === 'home' ? '1' : ($pick === 'draw' ? 'X' : '2'),
            'label'        => $pick === 'home' ? 'Home Win (1)' : ($pick === 'draw' ? 'Draw (X)' : 'Away Win (2)'),
            'confidence'   => min($conf, 99),
            'probabilities' => ['home' => round($s['home_win_prob'], 1), 'draw' => round($s['draw_prob'], 1), 'away' => round($s['away_win_prob'], 1)],
            'best_odds'    => $pick === 'home' ? $odds['home_odds'] : ($pick === 'draw' ? $odds['draw_odds'] : $odds['away_odds']),
        ];
    }

    protected function pDC(array $s): array
    {
        $op = ['1X' => $s['home_win_prob'] + $s['draw_prob'], 'X2' => $s['draw_prob'] + $s['away_win_prob'], '12' => $s['home_win_prob'] + $s['away_win_prob']];
        arsort($op);
        $pk = array_key_first($op);
        return ['pick' => $pk, 'confidence' => round(reset($op)), 'options' => ['1X' => round($op['1X']), '12' => round($op['12']), 'X2' => round($op['X2'])]];
    }

    protected function pBTS(array $s, array $odds): array
    {
        $pr = $s['bts_prob'];
        $pk = $pr >= 50 ? 'Yes' : 'No';
        return ['pick' => $pk, 'confidence' => $pk === 'Yes' ? round($pr) : round(100 - $pr), 'probability' => round($pr)];
    }

    protected function pOver(array $s, float $th, array $odds): array
    {
        $k = 'o' . str_replace('.', '', (string) ($th * 10)) . '_prob';
        $pr = $s[$k] ?? 50;
        $pk = $pr >= 50 ? 'Over' : 'Under';
        return ['pick' => $pk, 'threshold' => $th, 'confidence' => $pk === 'Over' ? round($pr) : round(100 - $pr), 'probability' => round($pr)];
    }

    protected function pDraw(array $s): array
    {
        return ['is_draw_likely' => $s['draw_prob'] >= 30, 'confidence' => round($s['draw_prob']), 'probability' => round($s['draw_prob'])];
    }

    protected function pCS(array $s): array
    {
        $h = round($s['hxg']); $a = round($s['axg']);
        $c = [];
        for ($i = max(0, $h - 1); $i <= $h + 1; $i++)
            for ($j = max(0, $a - 1); $j <= $a + 1; $j++)
                $c["{$i}-{$j}"] = round(max(0, 100 - (abs($i - $h) + abs($j - $a)) * 20));
        arsort($c);
        return ['most_likely' => array_key_first($c), 'candidates' => array_slice($c, 0, 5, true)];
    }

    protected function pHT(array $s): array
    {
        $hh = $s['hxg'] * 0.45; $ah = $s['axg'] * 0.45;
        if ($hh > $ah + 0.3) return ['pick' => '1', 'confidence' => 60];
        if ($ah > $hh + 0.3) return ['pick' => '2', 'confidence' => 60];
        return ['pick' => 'X', 'confidence' => 50];
    }

    // ═══════════════════════════════════════════════
    //  ANALYSIS
    // ═══════════════════════════════════════════════

    protected function analysis(Fixture $f, array $s, array $o, array $h2h, array $st, array $a): string
    {
        $p = [];
        $p[] = "⚽ **{$f->home_team} vs {$f->away_team}** — {$f->league_name}";

        if ($o['home_odds'] > 0)
            $p[] = "💰 **{$o['bookmaker']}**: H {$o['home_odds']} | D {$o['draw_odds']} | A {$o['away_odds']} | O2.5 {$o['over25_odds']} | BTTS Yes {$o['bts_yes']}";
        if ($a['advice'])
            $p[] = "🤖 **AI**: {$a['advice']}";
        if ($h2h['matches'] > 0)
            $p[] = "📋 **H2H**: {$h2h['matches']} matches, {$f->home_team} wins {$h2h['home_win_rate']}%, O2.5 {$h2h['over25_rate']}%, BTTS {$h2h['bts_rate']}%";
        $hf = $s['home_form']; $af = $s['away_form'];
        $p[] = "📈 **Form**: {$f->home_team} {$hf['form_string']} ({$hf['ppg']}ppg) | {$f->away_team} {$af['form_string']} ({$af['ppg']}ppg)";
        if ($st['home_position'] > 0)
            $p[] = "🏆 **Standings**: {$f->home_team} #{$st['home_position']} ({$st['home_points']}pts) | {$f->away_team} #{$st['away_position']} ({$st['away_points']}pts)";
        $p[] = "🎯 **xG**: {$f->home_team} {$s['hxg']} – {$s['axg']} {$f->away_team} (Total: {$s['txg']})";

        $best = $s['home_win_prob'] >= max($s['draw_prob'], $s['away_win_prob']) ? 'Home Win' : ($s['away_win_prob'] >= $s['draw_prob'] ? 'Away Win' : 'Draw');
        $c = max($s['home_win_prob'], $s['draw_prob'], $s['away_win_prob']);
        $p[] = "💡 **Pick**: {$best} ({$c}%)";

        return implode("\n\n", $p);
    }

    // ═══════════════════════════════════════════════
    //  STATS HELPERS
    // ═══════════════════════════════════════════════

    protected function analyzeH2H(int $h, int $a): array
    {
        $d = $this->api->getHeadToHead($h, $a, 10);
        if (!$d || empty($d['response'])) return ['matches' => 0, 'home_win_rate' => 0, 'away_win_rate' => 0, 'draw_rate' => 0, 'avg_home_goals' => 0, 'avg_away_goals' => 0, 'over25_rate' => 0, 'bts_rate' => 0];
        $hw = $aw = $dr = $hg = $ag = $o25 = $bts = 0;
        foreach ($d['response'] as $m) {
            $gh = $m['goals']['home'] ?? 0; $ga = $m['goals']['away'] ?? 0;
            $isH = ($m['teams']['home']['id'] ?? 0) == $h;
            $og = $isH ? $gh : $ga; $tg = $isH ? $ga : $gh;
            $hg += $og; $ag += $tg;
            if ($og > $tg) $hw++; elseif ($og < $tg) $aw++; else $dr++;
            if ($gh + $ga > 2.5) $o25++;
            if ($gh > 0 && $ga > 0) $bts++;
        }
        $t = count($d['response']);
        return ['matches' => $t, 'home_win_rate' => $t > 0 ? round($hw / $t * 100, 1) : 0, 'away_win_rate' => round($aw / $t * 100, 1), 'draw_rate' => round($dr / $t * 100, 1), 'avg_home_goals' => $t > 0 ? round($hg / $t, 2) : 0, 'avg_away_goals' => $t > 0 ? round($ag / $t, 2) : 0, 'over25_rate' => $t > 0 ? round($o25 / $t * 100, 1) : 0, 'bts_rate' => $t > 0 ? round($bts / $t * 100, 1) : 0];
    }

    protected function analyzeForm(int $tid): array
    {
        $d = $this->api->getTeamLastFixtures($tid, 10);
        if (!$d || empty($d['response'])) return ['wins' => 0, 'draws' => 0, 'losses' => 0, 'ppg' => 0, 'form_string' => '-----', 'goals_for' => 0, 'goals_against' => 0, 'over25_rate' => 0, 'bts_rate' => 0];
        $w = $dr = $l = $gf = $ga = $o25 = $bts = 0; $fm = [];
        foreach ($d['response'] as $f) {
            $isH = ($f['teams']['home']['id'] ?? 0) == $tid;
            $sc = $isH ? ($f['goals']['home'] ?? 0) : ($f['goals']['away'] ?? 0);
            $gc = $isH ? ($f['goals']['away'] ?? 0) : ($f['goals']['home'] ?? 0);
            $gf += $sc; $ga += $gc;
            if ($sc > $gc) { $w++; $fm[] = 'W'; }
            elseif ($sc < $gc) { $l++; $fm[] = 'L'; }
            else { $dr++; $fm[] = 'D'; }
            if ($sc + $gc > 2.5) $o25++;
            if ($sc > 0 && $gc > 0) $bts++;
        }
        $t = $w + $dr + $l;
        return ['wins' => $w, 'draws' => $dr, 'losses' => $l, 'ppg' => $t > 0 ? round(($w * 3 + $dr) / $t, 2) : 0, 'form_string' => implode('', array_slice($fm, 0, 5)), 'goals_for' => $gf, 'goals_against' => $ga, 'over25_rate' => $t > 0 ? round($o25 / $t * 100) : 0, 'bts_rate' => $t > 0 ? round($bts / $t * 100) : 0];
    }

    protected function getTeamStats(int $tid, int $lid, int $sid): array
    {
        $s = $this->api->getTeamStatistics($tid, $lid, $sid);
        if (!$s || empty($s['response'])) return $this->emptyTS();
        $d = $s['response'];
        if (isset($d[0])) $d = $d[0];
        $f = $d['fixtures'] ?? []; $g = $d['goals'] ?? [];
        return [
            'played_total' => $f['played']['total'] ?? 0,
            'avg_goals_for_home' => $g['for']['average']['home'] ?? '0',
            'avg_goals_for_away' => $g['for']['average']['away'] ?? '0',
            'avg_goals_against_home' => $g['against']['average']['home'] ?? '0',
            'avg_goals_against_away' => $g['against']['average']['away'] ?? '0',
            'bts_total' => $g['bts']['total'] ?? 0,
        ];
    }

    protected function emptyTS(): array
    {
        return ['played_total' => 0, 'avg_goals_for_home' => '0', 'avg_goals_for_away' => '0', 'avg_goals_against_home' => '0', 'avg_goals_against_away' => '0', 'bts_total' => 0];
    }

    protected function analyzeStandings(int $h, int $a, int $lid, int $sid): array
    {
        $d = $this->api->getStandings($lid, $sid);
        $hp = $ap = $hpt = $apt = 0;
        if ($d && !empty($d['response'])) {
            foreach ($d['response'][0]['league']['standings'][0] ?? [] as $t) {
                if (($t['team']['id'] ?? 0) == $h) { $hp = $t['rank']; $hpt = $t['points']; }
                if (($t['team']['id'] ?? 0) == $a) { $ap = $t['rank']; $apt = $t['points']; }
            }
        }
        return ['home_position' => $hp, 'away_position' => $ap, 'home_points' => $hpt, 'away_points' => $apt, 'position_diff' => $ap - $hp, 'points_diff' => $hpt - $apt];
    }
}
