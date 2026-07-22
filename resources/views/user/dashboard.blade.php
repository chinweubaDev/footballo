@extends('layouts.app')

@section('title', 'Dashboard - Football Predictions')

@section('content')
<div class="bg-slate-950 min-h-screen pb-20">
    <!-- Dashboard Header -->
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 pt-24 pb-16">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #3b82f6 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="absolute -top-24 -right-24 w-[500px] h-[500px] bg-blue-500/10 blur-[120px] rounded-full"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                <div class="text-center lg:text-left" data-aos="fade-right">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-black uppercase tracking-[0.2em] mb-8">
                        <i class="fas fa-chart-pie mr-2"></i> User Overview
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-black text-white mb-4 tracking-tight">
                        Welcome, <span class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 bg-clip-text text-transparent italic">{{ explode(' ', $user->name)[0] }}!</span>
                    </h1>
                    <p class="text-slate-400 text-lg max-w-xl">
                        Monitor your platform activity, track your predictions, and manage your premium subscription settings.
                    </p>
                </div>
                
                <div class="flex flex-col items-center lg:items-end gap-4" data-aos="fade-left">
                    @if($vvipStatus['is_active'])
                        <div class="group relative">
                            <div class="absolute inset-0 bg-purple-500/20 blur-xl rounded-2xl group-hover:bg-purple-500/30 transition-all"></div>
                            <div class="relative flex items-center px-8 py-4 bg-slate-900/80 border border-purple-500/30 rounded-2xl backdrop-blur-xl">
                                <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center mr-4 border border-purple-500/30">
                                    <i class="fas fa-gem text-purple-400 text-xl animate-pulse"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black text-purple-400 uppercase tracking-widest">Premium Status</div>
                                    <div class="text-white font-black text-xl tracking-tight">VVIP Member</div>
                                </div>
                            </div>
                        </div>
                    @elseif($vipStatus['is_active'])
                        <div class="group relative">
                            <div class="absolute inset-0 bg-blue-500/20 blur-xl rounded-2xl group-hover:bg-blue-500/30 transition-all"></div>
                            <div class="relative flex items-center px-8 py-4 bg-slate-900/80 border border-blue-500/30 rounded-2xl backdrop-blur-xl">
                                <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mr-4 border border-blue-500/30">
                                    <i class="fas fa-crown text-blue-400 text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Premium Status</div>
                                    <div class="text-white font-black text-xl tracking-tight">VIP Member</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="group relative">
                            <div class="absolute inset-0 bg-slate-500/20 blur-xl rounded-2xl group-hover:bg-slate-500/30 transition-all"></div>
                            <div class="relative flex items-center px-8 py-4 bg-slate-900/80 border border-white/10 rounded-2xl backdrop-blur-xl">
                                <div class="w-12 h-12 bg-slate-800 rounded-xl flex items-center justify-center mr-4 border border-white/10">
                                    <i class="fas fa-user-circle text-slate-400 text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Basic Access</div>
                                    <a href="{{ route('pricing') }}" class="text-white font-black text-lg hover:text-blue-400 transition-colors tracking-tight underline cursor-pointer">Upgrade Now</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Prediction Stat -->
            <div class="group relative" data-aos="fade-up" data-aos-delay="100">
                <div class="absolute inset-0 bg-blue-500/5 blur-xl group-hover:bg-blue-500/10 transition-all"></div>
                <div class="relative bg-slate-900/50 backdrop-blur-xl rounded-3xl p-6 border border-white/5 group-hover:border-blue-500/30 transition-all h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-all">
                            <i class="fas fa-chart-line text-2xl group-hover:text-white text-blue-500"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Total Signals</p>
                            <p class="text-3xl font-black text-white tracking-tighter counter" data-target="0">0</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Win Rate Stat -->
            <div class="group relative" data-aos="fade-up" data-aos-delay="200">
                <div class="absolute inset-0 bg-emerald-500/5 blur-xl group-hover:bg-emerald-500/10 transition-all"></div>
                <div class="relative bg-slate-900/50 backdrop-blur-xl rounded-3xl p-6 border border-white/5 group-hover:border-emerald-500/30 transition-all h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-emerald-500/10 rounded-2xl flex items-center justify-center border border-emerald-500/20 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                            <i class="fas fa-bullseye text-2xl group-hover:text-white text-emerald-500"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Success Rate</p>
                            <p class="text-3xl font-black text-white tracking-tighter counter" data-target="0">0%</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Revenue Stat -->
            <div class="group relative" data-aos="fade-up" data-aos-delay="300">
                <div class="absolute inset-0 bg-amber-500/5 blur-xl group-hover:bg-amber-500/10 transition-all"></div>
                <div class="relative bg-slate-900/50 backdrop-blur-xl rounded-3xl p-6 border border-white/5 group-hover:border-amber-500/30 transition-all h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-amber-500/10 rounded-2xl flex items-center justify-center border border-amber-500/20 group-hover:bg-amber-500 group-hover:text-white transition-all">
                            <i class="fas fa-wallet text-2xl group-hover:text-white text-amber-500"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Total Assets</p>
                            <p class="text-3xl font-black text-white tracking-tighter counter" data-target="0">$0</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Member Stat -->
            <div class="group relative" data-aos="fade-up" data-aos-delay="400">
                <div class="absolute inset-0 bg-indigo-500/5 blur-xl group-hover:bg-indigo-500/10 transition-all"></div>
                <div class="relative bg-slate-900/50 backdrop-blur-xl rounded-3xl p-6 border border-white/5 group-hover:border-indigo-500/30 transition-all h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-indigo-500/10 rounded-2xl flex items-center justify-center border border-indigo-500/20 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                            <i class="fas fa-calendar-check text-2xl group-hover:text-white text-indigo-500"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Member Tenure</p>
                            <p class="text-2xl font-black text-white tracking-tighter uppercase whitespace-nowrap">{{ $user->created_at->format('M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Subscription Status -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] border border-white/5 overflow-hidden" data-aos="fade-up">
                <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between bg-white/5">
                    <h2 class="text-xl font-black text-white flex items-center tracking-tight">
                        <i class="fas fa-crown mr-3 text-blue-500"></i>
                        Membership Status
                    </h2>
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Live Data</div>
                </div>
                <div class="p-8">
                    @if($vvipStatus['is_active'])
                        <!-- VVIP Active -->
                        <div class="relative group">
                            <div class="absolute inset-0 bg-purple-500/5 blur-3xl rounded-[2rem]"></div>
                            <div class="relative bg-slate-800/50 border border-purple-500/20 rounded-[2rem] p-8">
                                <div class="flex flex-col md:flex-row items-center gap-8 mb-8">
                                    <div class="w-20 h-20 bg-purple-500/10 rounded-2xl flex items-center justify-center border border-purple-500/20 shadow-lg shadow-purple-500/10">
                                        <i class="fas fa-gem text-purple-400 text-3xl"></i>
                                    </div>
                                    <div class="flex-1 text-center md:text-left">
                                        <h3 class="text-3xl font-black text-white tracking-tight mb-1">VVIP Elite Access</h3>
                                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[11px]">Unlimited Premium Coverage</p>
                                    </div>
                                    <div class="bg-slate-950/50 px-6 py-3 rounded-2xl border border-white/5 text-center">
                                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Renewal Date</p>
                                        <p class="text-white font-black">{{ $vvipStatus['expires_at']->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex justify-between items-end">
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Subscription Life</p>
                                        <p class="text-lg font-black text-purple-400 tracking-tighter">{{ $vvipStatus['days_remaining'] }} Days Left</p>
                                    </div>
                                    <div class="w-full bg-slate-950 rounded-full h-4 p-1 border border-white/5">
                                        <div class="bg-gradient-to-r from-purple-600 via-purple-500 to-indigo-500 h-2 rounded-full shadow-lg shadow-purple-500/20 transition-all duration-1000" 
                                             style="width: {{ $vvipStatus['days_remaining'] > 0 ? min(100, ($vvipStatus['days_remaining'] / 30) * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($vipStatus['is_active'])
                        <!-- VIP Active -->
                        <div class="relative group mb-8">
                            <div class="absolute inset-0 bg-blue-500/5 blur-3xl rounded-[2rem]"></div>
                            <div class="relative bg-slate-800/50 border border-blue-500/20 rounded-[2rem] p-8">
                                <div class="flex flex-col md:flex-row items-center gap-8 mb-8">
                                    <div class="w-20 h-20 bg-blue-500/10 rounded-2xl flex items-center justify-center border border-blue-500/20 shadow-lg shadow-blue-500/10">
                                        <i class="fas fa-crown text-blue-400 text-3xl"></i>
                                    </div>
                                    <div class="flex-1 text-center md:text-left">
                                        <h3 class="text-3xl font-black text-white tracking-tight mb-1">VIP Standard</h3>
                                        <p class="text-slate-400 font-bold uppercase tracking-widest text-[11px]">Premium Analytical Access</p>
                                    </div>
                                    <div class="bg-slate-950/50 px-6 py-3 rounded-2xl border border-white/5 text-center">
                                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Renewal Date</p>
                                        <p class="text-white font-black">{{ $vipStatus['expires_at']->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex justify-between items-end">
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Subscription Life</p>
                                        <p class="text-lg font-black text-blue-400 tracking-tighter">{{ $vipStatus['days_remaining'] }} Days Left</p>
                                    </div>
                                    <div class="w-full bg-slate-950 rounded-full h-4 p-1 border border-white/5">
                                        <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 h-2 rounded-full shadow-lg shadow-blue-500/20 transition-all duration-1000" 
                                             style="width: {{ $vipStatus['days_remaining'] > 0 ? min(100, ($vipStatus['days_remaining'] / 30) * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Upgrade Banner -->
                        <div class="relative overflow-hidden bg-gradient-to-r from-purple-600 to-indigo-700 rounded-3xl p-8 group">
                            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                                <i class="fas fa-gem text-9xl"></i>
                            </div>
                            <div class="relative z-10">
                                <h4 class="text-2xl font-black text-white mb-2 tracking-tight">Ascend to VVIP Elite</h4>
                                <p class="text-purple-100 text-sm mb-6 max-w-md opacity-80 italic font-medium">Unlock private consultations, maximum odds signals, and our most exclusive analytical clusters.</p>
                                <a href="{{ route('pricing') }}" class="inline-flex items-center px-8 py-4 bg-white text-slate-950 rounded-2xl hover:bg-slate-100 transition-all font-black text-xs uppercase tracking-widest shadow-xl">
                                    Unlock Elite Access <i class="fas fa-arrow-right ml-3"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <!-- No Active Subscription -->
                        <div class="text-center py-10">
                            <div class="w-24 h-24 bg-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-8 border border-white/5">
                                <i class="fas fa-lock text-slate-600 text-4xl"></i>
                            </div>
                            <h3 class="text-3xl font-black text-white mb-4 tracking-tight">Access Restricted</h3>
                            <p class="text-slate-400 mb-10 max-w-sm mx-auto font-medium italic">Your account is currently on the free tier. Upgrade to unlock expert-grade predictions and daily winning signals.</p>
                            <a href="{{ route('pricing') }}" class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-[2rem] hover:shadow-2xl hover:shadow-blue-500/40 transition-all font-black text-xs uppercase tracking-widest">
                                <i class="fas fa-rocket mr-3"></i>
                                Explore Premium Plans
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Access Command Center -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] border border-white/5 overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between bg-white/5">
                    <h2 class="text-xl font-black text-white flex items-center tracking-tight">
                        <i class="fas fa-th-large mr-3 text-blue-500"></i>
                        Quick Access Command Center
                    </h2>
                    <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Priority Hub</div>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                        <!-- VIP Signals -->
                        <a href="{{ route('tips.vip') }}" class="group relative bg-slate-800/50 border border-white/5 rounded-[2rem] p-6 transition-all hover:bg-slate-800 hover:-translate-y-1 hover:shadow-2xl hover:shadow-blue-500/10">
                            <div class="w-12 h-12 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-4 border border-blue-500/20 group-hover:bg-blue-500 transition-all">
                                <i class="fas fa-crown text-blue-400 text-xl group-hover:text-white"></i>
                            </div>
                            <h3 class="text-white font-black text-sm uppercase mb-1">VIP Signals</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Premium Tips</p>
                        </a>

                        <!-- Elite VVIP -->
                        <a href="{{ route('tips.vvip') }}" class="group relative bg-slate-800/50 border border-white/5 rounded-[2rem] p-6 transition-all hover:bg-slate-800 hover:-translate-y-1 hover:shadow-2xl hover:shadow-purple-500/10">
                            <div class="w-12 h-12 bg-purple-500/10 rounded-2xl flex items-center justify-center mb-4 border border-purple-500/20 group-hover:bg-purple-500 transition-all">
                                <i class="fas fa-gem text-purple-400 text-xl group-hover:text-white"></i>
                            </div>
                            <h3 class="text-white font-black text-sm uppercase mb-1">Elite VVIP</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Maximum Odds</p>
                        </a>

                        <!-- Today's Tips -->
                        <a href="{{ route('predictions.tomorrow') }}" class="group relative bg-slate-800/50 border border-white/5 rounded-[2rem] p-6 transition-all hover:bg-slate-800 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-500/10">
                            <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center mb-4 border border-emerald-500/20 group-hover:bg-emerald-500 transition-all">
                                <i class="far fa-calendar-alt text-emerald-400 text-xl group-hover:text-white"></i>
                            </div>
                            <h3 class="text-white font-black text-sm uppercase mb-1">Today</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Daily Tips</p>
                        </a>

                        <!-- All Forecasts -->
                        <a href="{{ route('predictions') }}" class="group relative bg-slate-800/50 border border-white/5 rounded-[2rem] p-6 transition-all hover:bg-slate-800 hover:-translate-y-1 hover:shadow-2xl hover:shadow-slate-500/20">
                            <div class="w-12 h-12 bg-slate-700/50 rounded-2xl flex items-center justify-center mb-4 border border-white/10 group-hover:bg-slate-700 transition-all">
                                <i class="fas fa-chart-line text-slate-400 text-xl group-hover:text-white"></i>
                            </div>
                            <h3 class="text-white font-black text-sm uppercase mb-1">All Forecasts</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Global Coverage</p>
                        </a>

                        <!-- Upgrade Access -->
                        <a href="{{ route('pricing') }}" class="group relative bg-slate-800/50 border border-white/5 rounded-[2rem] p-6 transition-all hover:bg-slate-800 hover:-translate-y-1 hover:shadow-2xl hover:shadow-amber-500/20">
                            <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center mb-4 border border-amber-500/20 group-hover:bg-amber-500 transition-all">
                                <i class="fas fa-rocket text-amber-500 text-xl group-hover:text-white"></i>
                            </div>
                            <h3 class="text-white font-black text-sm uppercase mb-1">Upgrade</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Unlock Premium</p>
                        </a>

                        <!-- Support Hub -->
                        <a href="{{ route('support') }}" class="group relative bg-slate-800/50 border border-white/5 rounded-[2rem] p-6 transition-all hover:bg-slate-800 hover:-translate-y-1 hover:shadow-2xl hover:shadow-indigo-500/10">
                            <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center mb-4 border border-indigo-500/20 group-hover:bg-indigo-500 transition-all">
                                <i class="fas fa-headset text-indigo-400 text-xl group-hover:text-white"></i>
                            </div>
                            <h3 class="text-white font-black text-sm uppercase mb-1">Support</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Help Center</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Quick Actions -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] border border-white/5 overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                <div class="px-8 py-6 border-b border-white/5 bg-white/5">
                    <h2 class="text-lg font-black text-white flex items-center tracking-tight uppercase">
                        Command Center
                    </h2>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 gap-4">
                        <a href="{{ route('predictions') }}" class="group flex items-center p-5 bg-slate-800/50 border border-white/5 rounded-3xl hover:bg-slate-800 transition-all hover:translate-x-1">
                            <div class="w-12 h-12 bg-blue-500/10 rounded-2xl flex items-center justify-center border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-all">
                                <i class="fas fa-chart-line text-blue-400 group-hover:text-white"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="font-black text-white text-sm uppercase tracking-tight">All Signals</h3>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">Explore Predictions</p>
                            </div>
                        </a>

                        @if($vipStatus['is_active'])
                        <a href="{{ route('tips.vip') }}" class="group flex items-center p-5 bg-blue-500/5 border border-blue-500/10 rounded-3xl hover:bg-blue-500/10 transition-all hover:translate-x-1">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-2xl flex items-center justify-center border border-blue-500/30">
                                <i class="fas fa-crown text-blue-400"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="font-black text-blue-400 text-sm uppercase tracking-tight">VIP Portal</h3>
                                <p class="text-[10px] font-bold text-blue-500/60 uppercase tracking-widest mt-0.5">Advanced Signals</p>
                            </div>
                        </a>
                        @endif

                        @if($vvipStatus['is_active'])
                        <a href="{{ route('tips.vvip') }}" class="group flex items-center p-5 bg-purple-500/5 border border-purple-500/10 rounded-3xl hover:bg-purple-500/10 transition-all hover:translate-x-1">
                            <div class="w-12 h-12 bg-purple-500/20 rounded-2xl flex items-center justify-center border border-purple-500/30">
                                <i class="fas fa-gem text-purple-400"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="font-black text-purple-400 text-sm uppercase tracking-tight">Elite Hub</h3>
                                <p class="text-[10px] font-bold text-purple-500/60 uppercase tracking-widest mt-0.5">Premium VVIP Tips</p>
                            </div>
                        </a>
                        @endif

                        <a href="{{ route('profile') }}" class="group flex items-center p-5 bg-slate-800/50 border border-white/5 rounded-3xl hover:bg-slate-800 transition-all hover:translate-x-1">
                            <div class="w-12 h-12 bg-slate-700 rounded-2xl flex items-center justify-center border border-white/5">
                                <i class="fas fa-sliders-h text-slate-400 group-hover:text-white"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="font-black text-white text-sm uppercase tracking-tight">Control Panel</h3>
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">Profile Settings</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Assets -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] border border-white/5 overflow-hidden" data-aos="fade-up" data-aos-delay="400">
                <div class="px-8 py-6 border-b border-white/5 bg-white/5">
                    <h2 class="text-lg font-black text-white uppercase tracking-tight flex items-center">
                        <i class="fas fa-shield-alt mr-3 text-blue-500"></i>
                        Identity
                    </h2>
                </div>
                <div class="p-8">
                    <div class="space-y-6">
                        <div class="bg-slate-950/50 p-5 rounded-2xl border border-white/5">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Encrypted Email</p>
                            <p class="font-bold text-white text-sm tracking-tight truncate">{{ $user->email }}</p>
                        </div>
                        <div class="bg-slate-950/50 p-5 rounded-2xl border border-white/5">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Authorization Status</p>
                            <div class="flex items-center mt-2">
                                @if($vvipStatus['is_active'])
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-purple-500/10 text-purple-400 border border-purple-500/20 shadow-lg shadow-purple-500/5">
                                        <i class="fas fa-gem mr-2 text-[8px]"></i>Elite VVIP
                                    </span>
                                @elseif($vipStatus['is_active'])
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-400 border border-blue-500/20 shadow-lg shadow-blue-500/5">
                                        <i class="fas fa-crown mr-2 text-[8px]"></i>Premium VIP
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-800 text-slate-400 border border-white/5">
                                        <i class="fas fa-user-circle mr-2 text-[8px]"></i>Basic Recruit
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
</div>
@endsection