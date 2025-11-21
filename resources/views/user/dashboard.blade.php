@extends('layouts.app')

@section('title', 'Dashboard - Football Predictions')

@section('content')
<!-- Dashboard Header -->
<section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <div class="text-center lg:text-left mb-8 lg:mb-0" data-aos="fade-up">
                <h1 class="text-4xl lg:text-5xl font-bold text-white mb-4">
                    Welcome back, 
                    <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">{{ $user->name }}</span>
                </h1>
                <p class="text-xl text-slate-300 mb-6">
                    Here's your personalized dashboard with all your predictions and account information.
                </p>
            </div>
            
            <div class="text-center lg:text-right" data-aos="fade-up" data-aos-delay="100">
                @if($vvipStatus['is_active'])
                    <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg">
                        <i class="fas fa-gem text-white text-xl mr-3"></i>
                        <div>
                            <div class="text-white font-semibold">VVIP Active</div>
                            <div class="text-purple-100 text-sm">Expires: {{ $vvipStatus['expires_at']->format('M d, Y') }}</div>
                        </div>
                    </div>
                @elseif($vipStatus['is_active'])
                    <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg">
                        <i class="fas fa-crown text-white text-xl mr-3"></i>
                        <div>
                            <div class="text-white font-semibold">VIP Active</div>
                            <div class="text-blue-100 text-sm">Expires: {{ $vipStatus['expires_at']->format('M d, Y') }}</div>
                        </div>
                    </div>
                @else
                    <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-slate-500 to-slate-600 rounded-xl shadow-lg">
                        <i class="fas fa-user text-white text-xl mr-3"></i>
                        <div>
                            <div class="text-white font-semibold">Free Account</div>
                            <a href="{{ route('pricing') }}" class="text-slate-200 text-sm hover:text-white underline">
                                Upgrade to VIP/VVIP
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200" data-aos="fade-up" data-aos-delay="100">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Total Predictions</p>
                    <p class="text-2xl font-bold text-slate-900">0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200" data-aos="fade-up" data-aos-delay="200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-trophy text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Win Rate</p>
                    <p class="text-2xl font-bold text-slate-900">0%</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200" data-aos="fade-up" data-aos-delay="300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-coins text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Total Spent</p>
                    <p class="text-2xl font-bold text-slate-900">$0</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-200" data-aos="fade-up" data-aos-delay="400">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar text-white text-lg"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Member Since</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $user->created_at->format('M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Subscription Status -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up">
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center">
                        <i class="fas fa-crown mr-3 text-primary-500"></i>
                        Subscription Status
                    </h2>
                </div>
                <div class="p-6">
                    @if($vvipStatus['is_active'])
                        <!-- VVIP Active -->
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-2xl p-6">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-gem text-white text-2xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-2xl font-bold text-slate-900">VVIP Plan</h3>
                                    <p class="text-slate-600">Ultimate Premium Access</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <p class="text-sm text-slate-600 mb-1">Expires</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $vvipStatus['expires_at']->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-600 mb-1">Days Remaining</p>
                                    <p class="text-lg font-semibold text-green-600">{{ $vvipStatus['days_remaining'] }} days</p>
                                </div>
                            </div>

                            <div class="w-full bg-slate-200 rounded-full h-3 mb-2">
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-300" 
                                     style="width: {{ $vvipStatus['days_remaining'] > 0 ? min(100, ($vvipStatus['days_remaining'] / 30) * 100) : 0 }}%"></div>
                            </div>
                            <p class="text-sm text-slate-600 text-center">
                                {{ $vvipStatus['days_remaining'] }} days remaining
                            </p>
                        </div>
                    @elseif($vipStatus['is_active'])
                        <!-- VIP Active -->
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-2xl p-6 mb-6">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center">
                                    <i class="fas fa-crown text-white text-2xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-2xl font-bold text-slate-900">VIP Plan</h3>
                                    <p class="text-slate-600">Premium Access</p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <p class="text-sm text-slate-600 mb-1">Expires</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $vipStatus['expires_at']->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-600 mb-1">Days Remaining</p>
                                    <p class="text-lg font-semibold text-green-600">{{ $vipStatus['days_remaining'] }} days</p>
                                </div>
                            </div>

                            <div class="w-full bg-slate-200 rounded-full h-3 mb-2">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300" 
                                     style="width: {{ $vipStatus['days_remaining'] > 0 ? min(100, ($vipStatus['days_remaining'] / 30) * 100) : 0 }}%"></div>
                            </div>
                            <p class="text-sm text-slate-600 text-center">
                                {{ $vipStatus['days_remaining'] }} days remaining
                            </p>
                        </div>
                        
                        <!-- Upgrade to VVIP -->
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-6 text-white">
                            <h4 class="text-xl font-bold mb-2">Want More?</h4>
                            <p class="text-purple-100 mb-4">Upgrade to VVIP for exclusive access to our most premium predictions and personal consultation.</p>
                            <a href="{{ route('pricing') }}" class="inline-flex items-center px-6 py-3 bg-white text-purple-600 rounded-xl hover:bg-purple-50 transition-colors duration-200 font-semibold">
                                <i class="fas fa-gem mr-2"></i>
                                Upgrade to VVIP
                            </a>
                        </div>
                    @else
                        <!-- No Active Subscription -->
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-crown text-slate-400 text-3xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-900 mb-4">No Active Subscription</h3>
                            <p class="text-slate-600 mb-6">Get access to our premium predictions and expert analysis.</p>
                            <a href="{{ route('pricing') }}" class="inline-flex items-center px-8 py-4 text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-200 font-semibold shadow-lg hover:shadow-xl">
                                <i class="fas fa-rocket mr-3"></i>
                                Get VIP/VVIP Access
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center">
                        <i class="fas fa-history mr-3 text-primary-500"></i>
                        Recent Activity
                    </h2>
                </div>
                <div class="p-6">
                    @if($recentPayments->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentPayments as $payment)
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-credit-card text-white"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-semibold text-slate-900">{{ $payment->plan_name ?? 'Premium Plan' }}</p>
                                        <p class="text-sm text-slate-600">{{ $payment->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-slate-900">${{ number_format($payment->amount, 2) }}</p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($payment->status === 'completed') bg-green-100 text-green-800
                                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-receipt text-slate-400 text-2xl"></i>
                            </div>
                            <p class="text-slate-600">No recent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center">
                        <i class="fas fa-bolt mr-3 text-primary-500"></i>
                        Quick Actions
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <a href="{{ route('predictions') }}" class="flex items-center p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-slate-900">View Predictions</h3>
                                <p class="text-sm text-slate-600">Browse all available predictions</p>
                            </div>
                        </a>

                        @if($vipStatus['is_active'])
                        <a href="{{ route('tips.vip') }}" class="flex items-center p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors duration-200">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-crown text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-slate-900">VIP Tips</h3>
                                <p class="text-sm text-slate-600">Access exclusive VIP predictions</p>
                            </div>
                        </a>
                        @endif

                        @if($vvipStatus['is_active'])
                        <a href="{{ route('tips.vvip') }}" class="flex items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors duration-200">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-gem text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-slate-900">VVIP Tips</h3>
                                <p class="text-sm text-slate-600">Access exclusive VVIP predictions</p>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('profile') }}" class="flex items-center p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="font-semibold text-slate-900">Edit Profile</h3>
                                <p class="text-sm text-slate-600">Update your account information</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="400">
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center">
                        <i class="fas fa-info-circle mr-3 text-primary-500"></i>
                        Account Info
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Email</p>
                            <p class="font-semibold text-slate-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Member Since</p>
                            <p class="font-semibold text-slate-900">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-600 mb-1">Account Status</p>
                            <div class="flex items-center">
                                @if($vvipStatus['is_active'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-gem mr-1"></i>VVIP
                                    </span>
                                @elseif($vipStatus['is_active'])
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-crown mr-1"></i>VIP
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-800">
                                        <i class="fas fa-user mr-1"></i>Free
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection