<?php

namespace App\Console\Commands;

use App\Models\PricingPlan;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramPostPromotion extends Command
{
    protected $signature = 'telegram:post-promotion';
    protected $description = 'Post VIP/VVIP premium plan promotions to the Telegram channel';

    public function handle(TelegramService $telegram): int
    {
        $this->info('Posting premium plan promotion...');

        // Get VIP and VVIP plans
        $vipPlans = PricingPlan::where('key', 'like', 'vip_%')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $vvipPlans = PricingPlan::where('key', 'like', 'vvip_%')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Build plan details
        $vipDetails = '';
        foreach ($vipPlans as $plan) {
            $features = is_string($plan->features) ? json_decode($plan->features, true) : ($plan->features ?? []);
            $featureList = !empty($features) ? '• ' . implode("\n• ", array_slice($features, 0, 3)) : '';
            $vipDetails .= "
<b>📌 {$plan->name}</b>
💰 \${$plan->price_usd} / {$plan->duration_days} days
{$featureList}
";
        }

        $vvipDetails = '';
        foreach ($vvipPlans as $plan) {
            $features = is_string($plan->features) ? json_decode($plan->features, true) : ($plan->features ?? []);
            $featureList = !empty($features) ? '• ' . implode("\n• ", array_slice($features, 0, 4)) : '';
            $vvipDetails .= "
<b>💎 {$plan->name}</b>
💰 \${$plan->price_usd} / {$plan->duration_days} days
{$featureList}
";
        }

        $pricingUrl = route('pricing');

        $message = "
━━━━━━━━━━━━━━━━━━━━━━
<b>🏆 UNLOCK PREMIUM ACCESS</b>
━━━━━━━━━━━━━━━━━━━━━━

<b>🚀 Why Go Premium?</b>
✅ 99-100% accurate predictions
✅ Daily accumulator tips (3 VIP / 5 VVIP)
✅ Combined odds for maximum returns
✅ Expert analysis & match insights

━━━━━━━━━━━━━━━━━━━━━━
<u>👑 VIP PLANS</u>
{$vipDetails}
━━━━━━━━━━━━━━━━━━━━━━
<u>💎 VVIP ELITE PLANS</u>
{$vvipDetails}
━━━━━━━━━━━━━━━━━━━━━━

🎯 <b>VIP Benefits:</b>
• 3 accumulator tickets daily (9 picks)
• High-confidence selections
• Combined odds up to 5.00+

💎 <b>VVIP Benefits:</b>
• 5 accumulator tickets daily (15 picks)
• Highest confidence picks only
• Combined odds up to 10.00+

━━━━━━━━━━━━━━━━━━━━━━
🔗 <a href='{$pricingUrl}'>CHOOSE YOUR PLAN →</a>

<i>📌 Join thousands of successful bettors</i>
<i>🎲 Bet responsibly • 18+</i>
";

        $sent = $telegram->sendMessage($message);

        if ($sent) {
            $vipCount = $vipPlans->count();
            $vvipCount = $vvipPlans->count();
            $this->info("Promotion posted: {$vipCount} VIP + {$vvipCount} VVIP plans featured.");
        } else {
            $this->error('Failed to post promotion to Telegram.');
            return 1;
        }

        return 0;
    }
}
