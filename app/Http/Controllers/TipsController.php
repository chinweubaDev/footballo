<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tip;

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

        // Fetch VIP tips from database
        $tips = Tip::vip()
            ->active()
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
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

        // Fetch VVIP tips from database
        $tips = Tip::vvip()
            ->active()
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tips.vvip', compact('tips'));
    }
}
