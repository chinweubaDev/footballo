<?php

namespace App\Console\Commands;

use App\Models\PricingPlan;
use Illuminate\Console\Command;

class UpdatePricingToVIP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pricing:update-to-vip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update pricing plans to VIP and VVIP structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating pricing plans to VIP/VVIP structure...');

        // Clear existing plans
        PricingPlan::truncate();

        // Create VIP plans
        $vipPlans = [
            [
                'name' => 'VIP Plan - 3 Days',
                'key' => 'vip_3_days',
                'price_usd' => 9.99,
                'price_ngn' => 14999,
                'price_kes' => 1499,
                'price_ghs' => 149,
                'price_zwl' => 99999,
                'price_zmw' => 149,
                'price_ugx' => 39999,
                'price_tzs' => 24999,
                'duration_days' => 3,
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates'],
                'sort_order' => 1
            ],
            [
                'name' => 'VIP Plan - 1 Week',
                'key' => 'vip_1_week',
                'price_usd' => 19.99,
                'price_ngn' => 29999,
                'price_kes' => 2999,
                'price_ghs' => 299,
                'price_zwl' => 199999,
                'price_zmw' => 299,
                'price_ugx' => 79999,
                'price_tzs' => 49999,
                'duration_days' => 7,
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access'],
                'sort_order' => 2
            ],
            [
                'name' => 'VIP Plan - 1 Month',
                'key' => 'vip_1_month',
                'price_usd' => 59.99,
                'price_ngn' => 89999,
                'price_kes' => 8999,
                'price_ghs' => 899,
                'price_zwl' => 599999,
                'price_zmw' => 899,
                'price_ugx' => 239999,
                'price_tzs' => 149999,
                'duration_days' => 30,
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access', 'Custom Alerts'],
                'sort_order' => 3
            ]
        ];

        // Create VVIP plans
        $vvipPlans = [
            [
                'name' => 'VVIP Plan - 3 Days',
                'key' => 'vvip_3_days',
                'price_usd' => 19.99,
                'price_ngn' => 29999,
                'price_kes' => 2999,
                'price_ghs' => 299,
                'price_zwl' => 199999,
                'price_zmw' => 299,
                'price_ugx' => 79999,
                'price_tzs' => 49999,
                'duration_days' => 3,
                'features' => ['VVIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Personal Consultant', 'Exclusive Access'],
                'sort_order' => 4
            ],
            [
                'name' => 'VVIP Plan - 1 Week',
                'key' => 'vvip_1_week',
                'price_usd' => 39.99,
                'price_ngn' => 59999,
                'price_kes' => 5999,
                'price_ghs' => 599,
                'price_zwl' => 399999,
                'price_zmw' => 599,
                'price_ugx' => 159999,
                'price_tzs' => 99999,
                'duration_days' => 7,
                'features' => ['VVIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Personal Consultant', 'Exclusive Access', 'Priority Support'],
                'sort_order' => 5
            ],
            [
                'name' => 'VVIP Plan - 1 Month',
                'key' => 'vvip_1_month',
                'price_usd' => 99.99,
                'price_ngn' => 149999,
                'price_kes' => 14999,
                'price_ghs' => 1499,
                'price_zwl' => 999999,
                'price_zmw' => 1499,
                'price_ugx' => 399999,
                'price_tzs' => 249999,
                'duration_days' => 30,
                'features' => ['VVIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Personal Consultant', 'Exclusive Access', 'Priority Support', 'Custom Alerts', 'Direct Line'],
                'sort_order' => 6
            ]
        ];

        // Create all plans
        foreach (array_merge($vipPlans, $vvipPlans) as $plan) {
            PricingPlan::create($plan);
            $this->info("✓ Created {$plan['name']}");
        }

        $this->info('Pricing plans updated successfully!');
    }
}
