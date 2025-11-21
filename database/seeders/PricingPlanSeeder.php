<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => '3 Days',
                'key' => '3_days',
                'price_usd' => 5.99,
                'price_ngn' => 8999,
                'price_kes' => 899,
                'price_ghs' => 89,
                'price_zwl' => 59999,
                'price_zmw' => 89,
                'price_ugx' => 22999,
                'price_tzs' => 14999,
                'duration_days' => 3,
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support'],
                'sort_order' => 1
            ],
            [
                'name' => '1 Week',
                'key' => '1_week',
                'price_usd' => 12.99,
                'price_ngn' => 19999,
                'price_kes' => 1999,
                'price_ghs' => 199,
                'price_zwl' => 129999,
                'price_zmw' => 199,
                'price_ugx' => 49999,
                'price_tzs' => 32999,
                'duration_days' => 7,
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates'],
                'sort_order' => 2
            ],
            [
                'name' => '1 Month',
                'key' => '1_month',
                'price_usd' => 39.99,
                'price_ngn' => 59999,
                'price_kes' => 5999,
                'price_ghs' => 599,
                'price_zwl' => 399999,
                'price_zmw' => 599,
                'price_ugx' => 149999,
                'price_tzs' => 99999,
                'duration_days' => 30,
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access', 'Custom Alerts'],
                'sort_order' => 3
            ],
            [
                'name' => '3 Months',
                'key' => '3_months',
                'price_usd' => 99.99,
                'price_ngn' => 149999,
                'price_kes' => 14999,
                'price_ghs' => 1499,
                'price_zwl' => 999999,
                'price_zmw' => 1499,
                'price_ugx' => 399999,
                'price_tzs' => 249999,
                'duration_days' => 90,
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access', 'Custom Alerts', 'Personal Consultant'],
                'sort_order' => 4
            ]
        ];

        foreach ($plans as $plan) {
            PricingPlan::create($plan);
        }
    }
}
