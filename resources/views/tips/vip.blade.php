@extends('layouts.app')

@section('title', 'VIP Tips')
@section('meta_description', 'Join our VIP club for exclusive daily football tips. Enjoy high strike rates and professional betting advice.')
@section('meta_keywords', 'vip tips, exclusive predictions, pro betting, daily winners, subscription tips')

@section('content')
<!-- VIP Header -->
<section class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-100 text-sm font-medium mb-8" data-aos="fade-up">
                <i class="fas fa-crown mr-2"></i>
                VIP Exclusive Access
            </div>
            
            <h1 class="text-5xl lg:text-6xl font-bold text-white mb-6" data-aos="fade-up" data-aos-delay="100">
                VIP Tips
            </h1>
            
            <p class="text-xl lg:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="200">
                Exclusive premium football predictions for VIP members. Get expert analysis and high-confidence tips.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center" data-aos="fade-up" data-aos-delay="300">
                <div class="inline-flex items-center px-6 py-3 bg-white/10 border border-white/20 text-white rounded-xl backdrop-blur-sm">
                    <i class="fas fa-chart-line mr-3"></i>
                    <span>2 VIP Accumulators (3 &amp; 5 odds)</span>
                </div>
                <div class="inline-flex items-center px-6 py-3 bg-white/10 border border-white/20 text-white rounded-xl backdrop-blur-sm">
                    <i class="fas fa-trophy mr-3"></i>
                    <span>Data-Driven Selections</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Access Control Check -->
    @if(!auth()->check())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6 mb-8" data-aos="fade-up">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-lock text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-red-800">Authentication Required</h3>
                    <p class="text-red-600">You must be logged in to access VIP tips.</p>
                </div>
            </div>
        </div>
    @elseif(!auth()->user()->hasActiveVIP())
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 mb-8" data-aos="fade-up">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-crown text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-yellow-800">VIP Subscription Required</h3>
                        <p class="text-yellow-600">You need an active VIP subscription to access these tips.</p>
                    </div>
                </div>
                <a href="{{ route('pricing') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors duration-200 font-semibold">
                    <i class="fas fa-crown mr-2"></i>
                    Upgrade to VIP
                </a>
            </div>
        </div>
    @endif

    @if(auth()->check() && auth()->user()->hasActiveVIP())
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- VIP Accumulator Tickets -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-slate-900 flex items-center">
                                <i class="fas fa-crown mr-3 text-blue-600"></i>
                                VIP Accumulator Tips
                            </h2>
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ count($tickets) }} Accas Today
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-xs text-slate-400 mb-4">Odds are bookmaker odds where available, otherwise model-implied fair odds (labelled "model odds"). Predictions carry no guarantee of winning.</p>
                        @if(count($tickets) > 0)
                            <div class="space-y-8">
                                @foreach($tickets as $ticket)
                                <div class="bg-gradient-to-br from-blue-50 to-white border-2 border-blue-200 rounded-2xl overflow-hidden hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="{{ $loop->index * 150 }}">
                                    {{-- Ticket Header --}}
                                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                                <i class="fas fa-ticket-alt text-white"></i>
                                            </div>
                                            <div>
                                                <span class="text-white text-xs font-black uppercase tracking-widest opacity-70">Accumulator #{{ $ticket['ticket_number'] }}</span>
                                                <h3 class="text-white font-black text-lg leading-tight">VIP Acca #{{ $ticket['ticket_number'] }} — Target {{ $ticket['target_odds'] }} odds</h3>
                                                @if(!($ticket['reached_target'] ?? true))
                                                    <span class="text-[9px] font-bold text-amber-300 uppercase tracking-wider">target not reached</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-white/60 text-[10px] font-black uppercase tracking-widest block">Total Odds</span>
                                            <span class="text-2xl font-black text-yellow-300">{{ $ticket['total_odds'] !== null ? number_format($ticket['total_odds'], 2) : '—' }}</span>
                                        </div>
                                    </div>

                                    {{-- Legs --}}
                                    <div class="p-6">
                                        <div class="space-y-3">
                                            @foreach($ticket['legs'] as $leg)
                                            <div class="flex items-center justify-between bg-white rounded-xl p-4 border border-slate-100 hover:border-blue-200 transition-colors">
                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                    <div class="w-1.5 h-10 bg-blue-500 rounded-full flex-shrink-0"></div>
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ $leg['fixture']->league_name }}</span>
                                                            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">{{ $leg['fixture']->match_date->format('H:i') }}</span>
                                                        </div>
                                                        <p class="text-sm font-bold text-slate-900 truncate">{{ $leg['fixture']->home_team }} vs {{ $leg['fixture']->away_team }}</p>
                                                        <span class="text-xs font-bold text-primary-600">{{ $leg['prediction']->tip }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-right flex-shrink-0 ml-4">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Odds</span>
                                                    <span class="text-lg font-black text-slate-900">{{ $leg['odds'] !== null ? number_format($leg['odds'], 2) : '—' }}</span>
                                                    @if(($leg['odds_source'] ?? '') === 'model')
                                                        <span class="text-[9px] font-bold text-amber-500 block">model odds</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>

                                        {{-- Ticket Footer --}}
                                        <div class="mt-4 flex items-center justify-between bg-blue-50 rounded-xl px-5 py-3">
                                            <div class="flex items-center gap-4">
                                                <div>
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Legs</span>
                                                    <span class="text-sm font-bold text-slate-900">{{ count($ticket['legs']) }} Matches</span>
                                                </div>
                                                <div class="w-px h-8 bg-blue-200"></div>
                                                <div>
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Combined Win Chance</span>
                                                    <span class="text-sm font-bold text-green-600">{{ $ticket['combined_probability'] !== null ? number_format($ticket['combined_probability'], 1).'%' : '—' }}</span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Combined Odds</span>
                                                <span class="text-xl font-black text-blue-600">{{ $ticket['total_odds'] !== null ? number_format($ticket['total_odds'], 2) : '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-20 h-20 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-info-circle text-blue-600 text-3xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-900 mb-4">No VIP Accumulators Available</h3>
                                <p class="text-slate-600 mb-6">Our analysts are preparing today's high-confidence accumulator picks. Check back shortly.</p>
                                <a href="{{ route('predictions') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors duration-200 font-semibold">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    View All Predictions
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- VIP Features Sidebar -->
            <div class="space-y-6">
                <!-- Subscription Status -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-slate-900 flex items-center">
                            <i class="fas fa-crown mr-2 text-blue-600"></i>
                            Your VIP Status
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Plan</span>
                                <span class="text-sm font-semibold text-blue-600">VIP</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Expires</span>
                                <span class="text-sm font-semibold text-slate-900">{{ auth()->user()->vip_expires_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Days Remaining</span>
                                <span class="text-sm font-semibold text-green-600">{{ auth()->user()->vip_expires_at->diffInDays(now()) }} days</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ auth()->user()->vip_expires_at->diffInDays(now()) > 0 ? min(100, (auth()->user()->vip_expires_at->diffInDays(now()) / 30) * 100) : 0 }}%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-2 text-center">
                                {{ auth()->user()->vip_expires_at->diffInDays(now()) }} days remaining
                            </p>
                        </div>
                    </div>
                </div>

                <!-- VIP Features -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-slate-900 flex items-center">
                            <i class="fas fa-star mr-2 text-blue-600"></i>
                            VIP Features
                        </h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">2 VIP Accumulators (3 &amp; 5 odds)</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Up to 5 matches per accumulator</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Highest-confidence Over 1.5 &amp; Double Chance picks</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Combined Odds Displayed</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Expert Analysis</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Upgrade to VVIP -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white" data-aos="fade-up" data-aos-delay="400">
                    <h3 class="text-xl font-bold mb-2">Want More?</h3>
                    <p class="text-purple-100 text-sm mb-4">Upgrade to VVIP for exclusive access to our most premium predictions and personal consultation.</p>
                    <a href="{{ route('pricing') }}" class="inline-flex items-center w-full justify-center px-6 py-3 bg-white text-purple-600 rounded-xl hover:bg-purple-50 transition-colors duration-200 font-semibold">
                        <i class="fas fa-gem mr-2"></i>
                        Upgrade to VVIP
                    </a>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="500">
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900 flex items-center">
                            <i class="fas fa-bolt mr-2 text-primary-500"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('predictions') }}" class="flex items-center p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                                <i class="fas fa-chart-line text-blue-500 mr-3"></i>
                                <span class="text-sm font-medium text-slate-700">All Predictions</span>
                            </a>
                            <a href="{{ route('dashboard') }}" class="flex items-center p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                                <i class="fas fa-tachometer-alt text-green-500 mr-3"></i>
                                <span class="text-sm font-medium text-slate-700">Dashboard</span>
                            </a>
                            <a href="{{ route('profile') }}" class="flex items-center p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                                <i class="fas fa-user text-purple-500 mr-3"></i>
                                <span class="text-sm font-medium text-slate-700">Profile</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection