<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramPostSurestTip extends Command
{
    protected $signature = 'telegram:post-surest-tip';
    protected $description = 'Post the single surest tip of the day to the Telegram channel';

    public function handle(TelegramService $telegram): int
    {
        $this->info('Looking for today\'s surest tip...');

        // Get the highest confidence prediction for today
        $prediction = Prediction::whereHas('fixture', function ($q) {
                $q->whereDate('match_date', today());
            })
            ->where('status', 'pending')
            ->orderBy('confidence', 'desc')
            ->first();

        if (!$prediction) {
            $this->warn('No predictions found for today.');
            return 0;
        }

        $fixture = $prediction->fixture;
        if (!$fixture) {
            $this->warn('Prediction has no associated fixture.');
            return 0;
        }

        $confidence = (int) $prediction->confidence;
        $odds = $prediction->odds ? number_format((float) $prediction->odds, 2) : 'N/A';
        $matchDate = $fixture->match_date->format('D, M d Y — H:i');

        $starRating = $confidence >= 95 ? '🌟🌟🌟🌟🌟' : ($confidence >= 85 ? '🌟🌟🌟🌟' : '🌟🌟🌟');

        $message = "
<b>🏆 SURE TIP OF THE DAY</b>

{$starRating}

<b>⚽ {$fixture->home_team} vs {$fixture->away_team}</b>
📅 {$matchDate}
🏟 {$fixture->league_name}

━━━━━━━━━━━━━━━
<b>🔮 Prediction:</b> {$prediction->tip}
<b>📊 Confidence:</b> {$confidence}%
<b>💰 Odds:</b> {$odds}
━━━━━━━━━━━━━━━

🔗 <a href='" . route('match.detail', $fixture->id) . "'>View Full Analysis →</a>

<i>📌 Bet responsibly • 18+</i>
";

        $sent = $telegram->sendMessage($message);

        if ($sent) {
            $this->info("Surest tip posted: {$fixture->home_team} vs {$fixture->away_team} ({$confidence}%)");
        } else {
            $this->error('Failed to post to Telegram.');
            return 1;
        }

        return 0;
    }
}
