<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramPostMatchResult extends Command
{
    protected $signature = 'telegram:post-match-result';
    protected $description = 'Post the winning ticket/result for yesterday\'s surest tip';

    public function handle(TelegramService $telegram): int
    {
        $this->info('Checking yesterday\'s surest tip result...');

        // Get the highest confidence prediction from yesterday (finished matches)
        $prediction = Prediction::whereHas('fixture', function ($q) {
                $q->whereDate('match_date', today()->subDay());
            })
            ->orderBy('confidence', 'desc')
            ->first();

        if (!$prediction) {
            $this->warn('No prediction found for yesterday.');
            return 0;
        }

        $fixture = $prediction->fixture;
        if (!$fixture) {
            $this->warn('Prediction has no associated fixture.');
            return 0;
        }

        // Only post if the match has finished
        if (!in_array($fixture->status, ['FT', 'AET', 'PEN'])) {
            $this->info("Match hasn't finished yet (status: {$fixture->status}). Skipping.");
            return 0;
        }

        $homeGoals = $fixture->home_goals ?? 0;
        $awayGoals = $fixture->away_goals ?? 0;
        $confidence = (int) $prediction->confidence;
        $odds = $prediction->odds ? number_format((float) $prediction->odds, 2) : 'N/A';

        // Determine if prediction won (settlement result lives in `result`).
        $won = ($prediction->result ?? $prediction->status) === 'won';

        $resultEmoji = $won ? '✅✅✅' : '❌❌❌';
        $resultText = $won ? 'WON ✅' : 'LOST ❌';
        $resultColor = $won ? '🟢' : '🔴';

        $message = "
<b>{$resultColor} MATCH RESULT {$resultColor}</b>

━━━━━━━━━━━━━━━
<b>⚽ {$fixture->home_team} vs {$fixture->away_team}</b>
🏟 {$fixture->league_name}

<b>📊 Final Score:</b>
<b>{$fixture->home_team}</b> {$homeGoals} — {$awayGoals} <b>{$fixture->away_team}</b>

━━━━━━━━━━━━━━━
<b>🔮 Our Tip:</b> {$prediction->tip}
<b>💰 Odds:</b> {$odds}
<b>📈 Confidence:</b> {$confidence}%
<b>🏆 Result:</b> {$resultText} {$resultEmoji}
━━━━━━━━━━━━━━━

{$this->getResultMessage($won, $confidence)}

<i>📊 Track record: High accuracy guaranteed</i>
";

        $sent = $telegram->sendMessage($message);

        if ($sent) {
            $this->info("Match result posted: {$fixture->home_team} {$homeGoals}-{$awayGoals} {$fixture->away_team} ({$resultText})");
        } else {
            $this->error('Failed to post match result to Telegram.');
            return 1;
        }

        return 0;
    }

    private function getResultMessage(bool $won, int $confidence): string
    {
        if ($won) {
            if ($confidence >= 95) {
                return "💎 <b>ELITE PICK — CONFIRMED!</b> Another premium-grade prediction hits the mark! Our {$confidence}% confidence algorithm delivers once again. 🏆";
            }
            return "✅ <b>WINNING PICK</b> — Our analysis proved accurate! Trust the process and stay tuned for more winners. 📈";
        }

        return "📝 <b>Analysis Note:</b> Even the best models face variance. Our algorithm maintains 85%+ accuracy over the long run. Stay confident! 🎯";
    }
}
