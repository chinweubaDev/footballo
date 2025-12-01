<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fixture;

class TipsController extends Controller
{
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

        // Fetch VIP fixtures from database
        $tips = Fixture::vip()
            ->with('predictions')
            ->where('match_date', '>=', now()->subHours(2)) // Show current/future matches
            ->orderBy('match_date', 'asc')
            ->paginate(10);

        return view('tips.vip', compact('tips'));
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

        // Fetch VVIP fixtures from database
        $tips = Fixture::vvip()
            ->with('predictions')
            ->where('match_date', '>=', now()->subHours(2))
            ->orderBy('match_date', 'asc')
            ->paginate(10);

        return view('tips.vvip', compact('tips'));
    }
}
