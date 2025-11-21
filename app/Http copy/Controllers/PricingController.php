<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        $plans = [
            '3_days' => [
                'name' => '3 Days',
                'price' => 5.99,
                'duration' => '3 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support']
            ],
            '1_week' => [
                'name' => '1 Week',
                'price' => 12.99,
                'duration' => '7 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates']
            ],
            '3_weeks' => [
                'name' => '3 Weeks',
                'price' => 29.99,
                'duration' => '21 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access']
            ],
            '1_month' => [
                'name' => '1 Month',
                'price' => 39.99,
                'duration' => '30 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access', 'Custom Alerts']
            ],
            '1_year' => [
                'name' => '1 Year',
                'price' => 299.99,
                'duration' => '365 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access', 'Custom Alerts', 'Personal Consultant']
            ]
        ];

        return view('pricing', compact('plans'));
    }
}
