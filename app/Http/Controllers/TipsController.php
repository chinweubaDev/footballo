<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fixture;
use App\Services\Prediction\AccumulatorBuilderService;
use Illuminate\Support\Collection;

class TipsController extends Controller
{
    public function __construct(protected AccumulatorBuilderService $accumulators)
    {
    }

    public function vip()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access VIP tips.');
        }

        // Check if user has active VIP subscription
        if (!Auth::user()->hasActiveVIP()) {
            return redirect()->route('pricing')->with('error', 'You need an active VIP subscription to access VIP tips.');
        }

        // VIP: 3-leg + 5-leg accumulators.
        $tickets = $this->accumulators->build('vip');

        return view('tips.vip', [
            'tickets' => $tickets,
            'totalPicks' => array_sum(array_column($tickets, 'leg_count')),
        ]);
    }

    public function vvip()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access VVIP tips.');
        }

        // Check if user has active VVIP subscription
        if (!Auth::user()->hasActiveVVIP()) {
            return redirect()->route('pricing')->with('error', 'You need an active VVIP subscription to access VVIP tips.');
        }

        // VVIP: 2-leg + 5-leg + 10-leg accumulators.
        $tickets = $this->accumulators->build('vvip');

        return view('tips.vvip', [
            'tickets' => $tickets,
            'totalPicks' => array_sum(array_column($tickets, 'leg_count')),
        ]);
    }
}
