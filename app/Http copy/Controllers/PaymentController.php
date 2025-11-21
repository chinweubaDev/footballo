<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function showPayment($plan)
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

        if (!isset($plans[$plan])) {
            return redirect()->route('pricing')->with('error', 'Invalid plan selected.');
        }

        $selectedPlan = $plans[$plan];
        $selectedPlan['key'] = $plan;

        return view('payment', compact('selectedPlan'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:3_days,1_week,3_weeks,1_month,1_year',
            'payment_method' => 'required|in:card,paypal',
        ]);

        $plans = [
            '3_days' => ['price' => 5.99, 'duration' => 3],
            '1_week' => ['price' => 12.99, 'duration' => 7],
            '3_weeks' => ['price' => 29.99, 'duration' => 21],
            '1_month' => ['price' => 39.99, 'duration' => 30],
            '1_year' => ['price' => 299.99, 'duration' => 365]
        ];

        $plan = $plans[$request->plan];
        
        // Create transaction
        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'plan' => $request->plan,
            'amount' => $plan['price'],
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'expires_at' => now()->addDays($plan['duration']),
        ]);

        // In a real application, you would integrate with payment gateways here
        // For demo purposes, we'll simulate a successful payment
        $transaction->update([
            'status' => 'completed',
            'transaction_id' => 'TXN_' . time(),
        ]);

        // Update user VIP status
        Auth::user()->update([
            'is_vip' => true,
            'vip_expires_at' => $transaction->expires_at,
        ]);

        return redirect()->route('dashboard')->with('success', 'Payment successful! Your VIP access has been activated.');
    }
}
