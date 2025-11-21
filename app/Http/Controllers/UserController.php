<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user's recent payments
        $recentPayments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get user's active subscription (legacy)
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
        
        // Get VIP/VVIP subscription status
        $vipStatus = [
            'is_active' => $user->hasActiveVIP(),
            'expires_at' => $user->vip_expires_at,
            'days_remaining' => $user->vip_expires_at ? $user->vip_expires_at->diffInDays(now()) : 0,
        ];
        
        $vvipStatus = [
            'is_active' => $user->hasActiveVVIP(),
            'expires_at' => $user->vvip_expires_at,
            'days_remaining' => $user->vvip_expires_at ? $user->vvip_expires_at->diffInDays(now()) : 0,
        ];
        
        return view('user.dashboard', compact('user', 'recentPayments', 'activeSubscription', 'vipStatus', 'vvipStatus'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'country']));

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        auth()->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('user.profile')->with('success', 'Password changed successfully!');
    }
}
