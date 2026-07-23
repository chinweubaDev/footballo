<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredUsers extends Command
{
    protected $signature = 'users:deactivate-expired';
    protected $description = 'Deactivate users whose VIP/VVIP/premium subscriptions have expired';

    public function handle(): int
    {
        $this->info('Checking for expired user subscriptions...');
        $deactivated = 0;

        // Find users where VIP has expired but is still marked active
        $expiredVip = User::where('is_vip_active', true)
            ->where('vip_expires_at', '<=', now())
            ->get();

        foreach ($expiredVip as $user) {
            $user->is_vip_active = false;
            $user->vip_expires_at = null;
            if ($user->subscription_type === 'vip') {
                $user->subscription_type = 'free';
            }
            $user->save();
            $deactivated++;
            $this->line("   Deactivated VIP for {$user->email}");
            Log::info('Expired VIP deactivated', ['user_id' => $user->id, 'email' => $user->email]);
        }

        // Find users where VVIP has expired but is still marked active
        $expiredVvip = User::where('is_vvip_active', true)
            ->where('vvip_expires_at', '<=', now())
            ->get();

        foreach ($expiredVvip as $user) {
            $user->is_vvip_active = false;
            $user->vvip_expires_at = null;
            if ($user->subscription_type === 'vvip') {
                $user->subscription_type = 'free';
            }
            $user->save();
            $deactivated++;
            $this->line("   Deactivated VVIP for {$user->email}");
            Log::info('Expired VVIP deactivated', ['user_id' => $user->id, 'email' => $user->email]);
        }

        // Find users where premium has expired
        $expiredPremium = User::where('is_premium', true)
            ->where('premium_expires_at', '<=', now())
            ->where('is_vip_active', false)
            ->where('is_vvip_active', false)
            ->get();

        foreach ($expiredPremium as $user) {
            $user->is_premium = false;
            $user->premium_expires_at = null;
            $user->subscription_type = 'free';
            $user->save();
            $deactivated++;
            $this->line("   Deactivated premium for {$user->email}");
            Log::info('Expired premium deactivated', ['user_id' => $user->id, 'email' => $user->email]);
        }

        $this->info("✅ Done. {$deactivated} user(s) deactivated.");
        return 0;
    }
}
