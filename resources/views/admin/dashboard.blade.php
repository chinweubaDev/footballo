@extends('layouts.app')

@section('title', 'Admin Dashboard - Football Predictions')

@section('content')
@section('content')
<div class="bg-slate-950 min-h-screen pb-20">
    <!-- Admin Hero -->
    <section class="relative overflow-hidden bg-gradient-to-b from-purple-900/20 via-slate-900 to-slate-950 pt-24 pb-16">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #a855f7 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="absolute -top-24 -right-24 w-[500px] h-[500px] bg-purple-500/10 blur-[120px] rounded-full"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                <div class="text-center lg:text-left" data-aos="fade-right">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-black uppercase tracking-[0.2em] mb-8">
                        <i class="fas fa-shield-alt mr-2"></i> Administrative Control
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-black text-white mb-4 tracking-tight">
                        Admin <span class="bg-gradient-to-r from-purple-400 via-purple-500 to-purple-600 bg-clip-text text-transparent italic">Terminal</span>
                    </h1>
                    <p class="text-slate-400 text-lg max-w-xl">
                        Manage users, track revenue, and monitor platform performance from your unified command center.
                    </p>
                </div>
                
                <div class="flex flex-col items-center lg:items-end gap-4" data-aos="fade-left">
                    <div class="group relative">
                        <div class="absolute inset-0 bg-purple-500/20 blur-xl rounded-2xl"></div>
                        <div class="relative flex items-center px-8 py-4 bg-slate-900/80 border border-purple-500/30 rounded-2xl backdrop-blur-xl">
                            <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center mr-4 border border-purple-500/30">
                                <i class="fas fa-user-shield text-purple-400 text-xl"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-black text-purple-400 uppercase tracking-widest">Access Mode</div>
                                <div class="text-white font-black text-xl tracking-tight">Root Admin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <!-- Platform Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Total Users -->
            <div class="group relative" data-aos="fade-up" data-aos-delay="100">
                <div class="absolute inset-0 bg-blue-500/5 blur-xl group-hover:bg-blue-500/10 transition-all"></div>
                <div class="relative bg-slate-900/50 backdrop-blur-xl rounded-3xl p-6 border border-white/5 group-hover:border-blue-500/30 transition-all h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center border border-blue-500/20 group-hover:bg-blue-500 transition-all">
                            <i class="fas fa-users text-2xl text-blue-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Total Signals Recipients</p>
                            <p class="text-3xl font-black text-white tracking-tighter">{{ $stats['total_users'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Premium Users -->
            <div class="group relative" data-aos="fade-up" data-aos-delay="200">
                <div class="absolute inset-0 bg-emerald-500/5 blur-xl group-hover:bg-emerald-500/10 transition-all"></div>
                <div class="relative bg-slate-900/50 backdrop-blur-xl rounded-3xl p-6 border border-white/5 group-hover:border-emerald-500/30 transition-all h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-emerald-500/10 rounded-2xl flex items-center justify-center border border-emerald-500/20 group-hover:bg-emerald-500 transition-all">
                            <i class="fas fa-crown text-2xl text-emerald-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Active Premium</p>
                            <p class="text-3xl font-black text-white tracking-tighter">{{ $stats['premium_users'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Revenue -->
            <div class="group relative lg:col-span-1" data-aos="fade-up" data-aos-delay="300">
                <div class="absolute inset-0 bg-amber-500/5 blur-xl group-hover:bg-amber-500/10 transition-all"></div>
                <div class="relative bg-slate-900/50 backdrop-blur-xl rounded-3xl p-6 border border-white/5 group-hover:border-amber-500/30 transition-all h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-amber-500/10 rounded-2xl flex items-center justify-center border border-amber-500/20 group-hover:bg-amber-500 transition-all">
                            <i class="fas fa-wallet text-2xl text-amber-500 group-hover:text-white"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Gross Revenue</p>
                            <p class="text-2xl font-black text-white tracking-tighter">NGN {{ number_format($stats['total_payments']) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Fixtures -->
            <div class="group relative" data-aos="fade-up" data-aos-delay="400">
                <div class="absolute inset-0 bg-indigo-500/5 blur-xl group-hover:bg-indigo-500/10 transition-all"></div>
                <div class="relative bg-slate-900/50 backdrop-blur-xl rounded-3xl p-6 border border-white/5 group-hover:border-indigo-500/30 transition-all h-full">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-indigo-500/10 rounded-2xl flex items-center justify-center border border-indigo-500/20 group-hover:bg-indigo-500 transition-all">
                            <i class="fas fa-futbol text-2xl text-indigo-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Signal Inventory</p>
                            <p class="text-3xl font-black text-white tracking-tighter">{{ $stats['total_fixtures'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operational Health -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <!-- Today's Tips -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl p-4 border border-white/5 flex items-center gap-4">
                <div class="w-10 h-10 bg-orange-500/10 rounded-xl flex items-center justify-center border border-orange-500/20">
                    <i class="fas fa-star text-orange-500"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Transmitted Today</p>
                    <p class="text-xl font-black text-white tracking-tight">{{ $stats['today_tips'] }} Tips</p>
                </div>
            </div>

            <!-- Featured -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl p-4 border border-white/5 flex items-center gap-4">
                <div class="w-10 h-10 bg-pink-500/10 rounded-xl flex items-center justify-center border border-pink-500/20">
                    <i class="fas fa-award text-pink-500"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Featured Active</p>
                    <p class="text-xl font-black text-white tracking-tight">{{ $stats['featured_predictions'] }} Verified</p>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-2xl p-4 border border-white/5 flex items-center gap-4">
                <div class="w-10 h-10 bg-rose-500/10 rounded-xl flex items-center justify-center border border-rose-500/20">
                    <i class="fas fa-hourglass-half text-rose-500"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Awaiting Confirmation</p>
                    <p class="text-xl font-black text-rose-400 tracking-tight">{{ $stats['pending_payments'] }} Payments</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white/5 mb-12 shadow-2xl">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-black text-white flex items-center tracking-tight uppercase">
                    Platform Management
                </h2>
                <div class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Operational Controls</div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Manage Fixtures -->
                <a href="{{ route('admin.fixtures') }}" class="group relative overflow-hidden bg-slate-800/50 border border-white/5 p-6 rounded-3xl hover:bg-slate-800 transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fas fa-futbol text-6xl"></i>
                    </div>
                    <div class="relative flex items-start gap-5">
                        <div class="w-12 h-12 bg-blue-500/10 rounded-2xl flex items-center justify-center border border-blue-500/20 group-hover:bg-blue-500 group-hover:text-white transition-all">
                            <i class="fas fa-futbol text-blue-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-white text-sm uppercase tracking-tight mb-1">Signal Forge</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Manage & Add Match Fixtures</p>
                        </div>
                    </div>
                </a>

                <!-- View Payments -->
                <a href="{{ route('admin.payments') }}" class="group relative overflow-hidden bg-slate-800/50 border border-white/5 p-6 rounded-3xl hover:bg-slate-800 transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fas fa-credit-card text-6xl"></i>
                    </div>
                    <div class="relative flex items-start gap-5">
                        <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center border border-emerald-500/20 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                            <i class="fas fa-credit-card text-emerald-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-white text-sm uppercase tracking-tight mb-1">Treasury</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Revenue & Subscription Analytics</p>
                        </div>
                    </div>
                </a>

                <!-- Manage Users -->
                <a href="{{ route('admin.users') }}" class="group relative overflow-hidden bg-slate-800/50 border border-white/5 p-6 rounded-3xl hover:bg-slate-800 transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fas fa-users text-6xl"></i>
                    </div>
                    <div class="relative flex items-start gap-5">
                        <div class="w-12 h-12 bg-purple-500/10 rounded-2xl flex items-center justify-center border border-purple-500/20 group-hover:bg-purple-500 group-hover:text-white transition-all">
                            <i class="fas fa-users text-purple-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-white text-sm uppercase tracking-tight mb-1">Citizenship</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Member Identity & Access Management</p>
                        </div>
                    </div>
                </a>

                <!-- VIP Results -->
                <a href="{{ route('admin.results') }}" class="group relative overflow-hidden bg-slate-800/50 border border-white/5 p-6 rounded-3xl hover:bg-slate-800 transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fas fa-chart-line text-6xl"></i>
                    </div>
                    <div class="relative flex items-start gap-5">
                        <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center border border-amber-500/20 group-hover:bg-amber-500 group-hover:text-white transition-all">
                            <i class="fas fa-chart-line text-amber-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-white text-sm uppercase tracking-tight mb-1">Verified Truth</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Publish & Audit Winning Results</p>
                        </div>
                    </div>
                </a>

                <!-- Manual Subscriptions -->
                <a href="{{ route('admin.user-subscriptions') }}" class="group relative overflow-hidden bg-slate-800/50 border border-white/5 p-6 rounded-3xl hover:bg-slate-800 transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fas fa-key text-6xl"></i>
                    </div>
                    <div class="relative flex items-start gap-5">
                        <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center border border-indigo-500/20 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                            <i class="fas fa-key text-indigo-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-white text-sm uppercase tracking-tight mb-1">Access Keys</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Manual Plan Provisioning</p>
                        </div>
                    </div>
                </a>

                <!-- Quick Add -->
                <button onclick="showAddFixtureModal()" class="group relative overflow-hidden bg-slate-800/50 border border-white/5 p-6 rounded-3xl hover:bg-slate-800 transition-all hover:-translate-y-1 text-left">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fas fa-plus text-6xl"></i>
                    </div>
                    <div class="relative flex items-start gap-5">
                        <div class="w-12 h-12 bg-emerald-500/10 rounded-2xl flex items-center justify-center border border-emerald-500/20 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                            <i class="fas fa-plus text-emerald-500 group-hover:text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-white text-sm uppercase tracking-tight mb-1">Instant Forge</h3>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Quick Add New Transmission</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Transmissions -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white/5 shadow-2xl">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-white flex items-center tracking-tight uppercase">
                        Recent Signals
                    </h2>
                </div>
                <div class="space-y-4">
                    <div class="text-center py-12 bg-slate-800/20 rounded-3xl border border-dashed border-white/10">
                        <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-satellite-dish text-slate-600 text-2xl"></i>
                        </div>
                        <p class="text-slate-500 font-black uppercase tracking-widest text-[10px]">No active signals in current window</p>
                        <a href="{{ route('admin.fixtures') }}" class="text-blue-500 hover:text-blue-400 text-[10px] font-black uppercase tracking-widest mt-4 inline-block underline">
                            Initialize Signal <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Core Infrastructure -->
            <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white/5 shadow-2xl">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-white flex items-center tracking-tight uppercase">
                        Core Systems
                    </h2>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                        Stable
                    </span>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-5 bg-slate-800/50 rounded-2xl border border-white/5">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Football API Hub</span>
                        </div>
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Connected</span>
                    </div>
                    <div class="flex items-center justify-between p-5 bg-slate-800/50 rounded-2xl border border-white/5">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Treasury Gateway</span>
                        </div>
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Active</span>
                    </div>
                    <div class="flex items-center justify-between p-5 bg-slate-800/50 rounded-2xl border border-white/5">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Communication Uplink</span>
                        </div>
                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Awaiting Config</span>
                    </div>
                    <div class="flex items-center justify-between p-5 bg-slate-800/50 rounded-2xl border border-white/5">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Central Database</span>
                        </div>
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Healthy</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

<!-- Add Fixture Modal -->
<div id="addFixtureModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md overflow-y-auto h-full w-full hidden z-[100]">
    <div class="relative top-20 mx-auto p-12 border border-white/10 w-full max-w-2xl shadow-2xl rounded-[2.5rem] bg-slate-900/90 backdrop-blur-xl">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h3 class="text-2xl font-black text-white tracking-tight uppercase mb-1">Initialize Signal</h3>
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Add New Broadcast Entry</p>
                </div>
                <button onclick="hideAddFixtureModal()" class="w-10 h-10 bg-slate-800 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-all border border-white/5">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addFixtureForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Geographic Zone</label>
                        <select id="country" class="w-full bg-slate-950/50 border border-white/5 rounded-2xl px-5 py-4 text-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none appearance-none">
                            <option value="">Select Territory</option>
                            <option value="England">England</option>
                            <option value="Spain">Spain</option>
                            <option value="Germany">Germany</option>
                            <option value="Italy">Italy</option>
                            <option value="France">France</option>
                            <option value="Nigeria">Nigeria</option>
                            <option value="Ghana">Ghana</option>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Competitive League</label>
                        <select id="league" class="w-full bg-slate-950/50 border border-white/5 rounded-2xl px-5 py-4 text-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none appearance-none">
                            <option value="">Select Tier</option>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Temporal Season</label>
                        <select id="season" class="w-full bg-slate-950/50 border border-white/5 rounded-2xl px-5 py-4 text-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none appearance-none">
                            <option value="2024">Current Cycle (2024)</option>
                            <option value="2023">Previous Cycle (2023)</option>
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Start Threshold</label>
                        <input type="date" id="from_date" class="w-full bg-slate-950/50 border border-white/5 rounded-2xl px-5 py-4 text-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                    </div>
                    
                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">End Threshold</label>
                        <input type="date" id="to_date" class="w-full bg-slate-950/50 border border-white/5 rounded-2xl px-5 py-4 text-white text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                    </div>
                </div>
                
                <div class="mt-12 flex items-center justify-end gap-4">
                    <button type="button" onclick="hideAddFixtureModal()" class="px-8 py-4 bg-slate-800 text-slate-400 rounded-2xl hover:bg-slate-700 hover:text-white transition-all font-black text-[10px] uppercase tracking-widest border border-white/5">
                        Abort
                    </button>
                    <button type="button" onclick="fetchFixtures()" class="px-10 py-4 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-500 transition-all font-black text-[10px] uppercase tracking-widest shadow-xl shadow-emerald-500/20">
                        Synchronize Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Admin dashboard JavaScript is now handled by app.js -->
@endsection
