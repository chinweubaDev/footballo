@extends('layouts.app')

@section('title', 'Best Football Prediction Website - Winning Tips for Today')
@section('meta_description', 'Esurebet is the best football prediction website offering sure tips prediction and accurate football prediction for today and tomorrow. Get your bet tips now!')

@section('content')
<main id="main-content">
<!-- Hero Section -->
<section class="relative min-h-[80vh] flex items-center overflow-hidden bg-slate-900 pt-20 pb-20">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #22c55e 1px, transparent 0); background-size: 32px 32px;"></div>
    </div>
    <!-- Animated background objects -->
    <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-[500px] h-[500px] bg-primary-500/20 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-[400px] h-[400px] bg-blue-500/10 blur-[100px] rounded-full"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div data-aos="fade-right">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-400 text-sm font-semibold mb-8">
                    <span class="relative flex h-3 w-3 mr-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-primary-500"></span>
                    </span>
                    Live Predictions Available Now
                </div>
                <h1 class="text-5xl lg:text-7xl font-extrabold text-white mb-6 leading-tight">
                    Win Big with the <br>
                    <span class="bg-gradient-to-r from-primary-400 via-primary-500 to-primary-600 bg-clip-text text-transparent">Best Betting Tip</span> Website
                </h1>
                <p class="text-xl text-slate-300 mb-10 leading-relaxed max-w-xl">
                    Experience the next level of football betting with 85%+ accuracy rates, in-depth statistical analysis, and exclusive VIP winning strategies.
                </p>
                <div class="flex flex-col sm:flex-row gap-5">
                    <a href="{{ route('predictions') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-lg shadow-primary-500/25 transform hover:-translate-y-1">
                        <i class="fas fa-chart-line mr-3"></i> View Today's Tips
                    </a>
                    <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white bg-slate-800 border border-slate-700 rounded-2xl hover:bg-slate-700 transition-all duration-300 transform hover:-translate-y-1">
                        <i class="fas fa-crown text-yellow-400 mr-3"></i> Get VIP Access
                    </a>
                </div>
                
                <div class="mt-12 flex items-center space-x-8">
                    <!-- <div class="flex -space-x-3">
                        @for($i = 1; $i <= 5; $i++)
                            <img class="w-10 h-10 rounded-full border-2 border-slate-900 bg-slate-800" src="https://ui-avatars.com/api/?name=User+{{$i}}&background=random" alt="User {{$i}}">
                        @endfor
                        <div class="w-10 h-10 rounded-full border-2 border-slate-900 bg-primary-600 flex items-center justify-center text-xs font-bold text-white uppercase tracking-tighter">
                            10k+
                        </div>
                    </div> -->
                    <!-- <div class="text-slate-400 text-sm">
                        <div class="flex items-center text-yellow-400 mb-0.5">
                            @for($i = 0; $i < 5; $i++) <i class="fas fa-star text-xs"></i> @endfor
                        </div>
                        Trusted by 10,000+ active users
                    </div> -->
                </div>
            </div>

            <!-- Dashboard Preview / Mockup -->
            <div data-aos="fade-left" class="relative hidden lg:block">
                <div class="absolute -inset-4 bg-gradient-to-tr from-primary-500/30 to-blue-500/30 blur-3xl rounded-[3rem]"></div>
                <div class="relative bg-slate-800/50 backdrop-blur-xl border border-white/10 rounded-[2.5rem] p-6 shadow-2xl overflow-hidden group">
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-white/5">
                        <div class="flex space-x-2">
                            <div class="w-3 h-3 rounded-full bg-red-500/50"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/50"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/50"></div>
                        </div>
                        <div class="text-xs font-medium text-slate-500 uppercase tracking-widest">LIVE SCOREBOARD</div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-white/5 rounded-2xl border border-white/5 hover:bg-white/10 transition-colors">
                            <div class="flex justify-between items-center text-xs text-slate-500 mb-3">
                                <span>Premier League</span>
                                <span class="text-primary-400 font-bold flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-400 mr-1.5 animate-pulse"></span> 78'
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-white/10"></div>
                                    <span class="text-white font-bold">Liverpool</span>
                                </div>
                                <span class="text-2xl font-black text-white px-3">2 - 1</span>
                                <div class="flex items-center space-x-3">
                                    <span class="text-white font-bold">Chelsea</span>
                                    <div class="w-8 h-8 rounded-lg bg-white/10"></div>
                                </div>
                            </div>
                        </div>
                        <!-- More mockup items... -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Grid -->
<section class="relative z-10 -mt-10 mb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
            <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col items-center text-center group hover:border-primary-500/30 transition-all duration-300" data-aos="fade-up" data-aos-delay="0">
                <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 mb-4 group-hover:bg-primary-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-bolt text-xl"></i>
                </div>
                <div class="text-3xl font-black text-slate-900 mb-1">97%</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Win Rate</div>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col items-center text-center group hover:border-primary-500/30 transition-all duration-300" data-aos="fade-up" data-aos-delay="100">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="text-3xl font-black text-slate-900 mb-1">10k+</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Active Users</div>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col items-center text-center group hover:border-primary-500/30 transition-all duration-300" data-aos="fade-up" data-aos-delay="200">
                <div class="w-12 h-12 bg-yellow-50 rounded-2xl flex items-center justify-center text-yellow-600 mb-4 group-hover:bg-yellow-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
                <div class="text-3xl font-black text-slate-900 mb-1">150+</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daily Tips</div>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col items-center text-center group hover:border-primary-500/30 transition-all duration-300" data-aos="fade-up" data-aos-delay="300">
                <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                    <i class="fas fa-headset text-xl"></i>
                </div>
                <div class="text-3xl font-black text-slate-900 mb-1">24/7</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Support</div>
            </div>
        </div>
    </div>
</section>

<!-- Free Tips Table -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-sm font-black text-primary-600 uppercase tracking-[0.2em] mb-3">Daily Forecasts</h2>
            <h3 class="text-4xl lg:text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">Football Predictions & Tips for Today, {{ now()->format('F jS, Y') }}</h3>
            <p class="text-lg text-slate-500 max-w-2xl mx-auto">Get access to professional quality predictions for today's most popular football matches at zero cost.</p>
        </div>

        @if($todayTipsByLeague->count() > 0)
            <div class="space-y-12">
                @foreach($todayTipsByLeague as $leagueName => $fixtures)
                <div class="bg-slate-50 rounded-[2.5rem] p-1 border border-slate-100 overflow-hidden" data-aos="fade-up">
                    <div class="p-6 md:px-10 flex items-center space-x-4 border-b border-slate-200/50">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                            <img src="{{ $fixtures[0]['league_logo'] }}" alt="{{ $leagueName }}" class="w-8 h-8 object-contain opacity-80">
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-primary-600 uppercase tracking-widest">{{ $fixtures[0]['league_country'] }}</span>
                            <h4 class="text-xl font-black text-slate-900">{{ $leagueName }}</h4>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left responsive-table">
                            <thead class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <tr>
                                    <th class="px-6 py-5">Time</th>
                                    <th class="px-6 py-5">Match</th>
                                    <th class="px-6 py-5 text-center">Score</th>
                                    <th class="px-6 py-5">Tip</th>
                                    <th class="px-6 py-5 text-right pr-10">Odds</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/50">
                                @foreach($fixtures as $fixture)
                                    @foreach($fixture->predictions as $prediction)
                                    @php
                                        $sClass = match($fixture->status) {
                                            'FT','AET','PEN' => 'bg-green-50 text-green-700',
                                            'LIVE','1H','HT','2H' => 'bg-red-50 text-red-600',
                                            default => 'text-slate-300'
                                        };
                                        $sLabel = match($fixture->status) {
                                            'FT','AET','PEN' => 'FT',
                                            'LIVE','1H','HT','2H' => 'LIVE',
                                            default => ''
                                        };
                                    @endphp
                                    <tr class="hover:bg-white transition-colors group cursor-pointer" onclick="window.location='{{ route('match.detail', $fixture->id) }}'">
                                        <td class="px-6 py-4 border-l-4 border-transparent group-hover:border-primary-500 transition-all" data-label="Time">
                                            <span class="font-bold text-slate-900 block">{{ $fixture->match_date->format('H:i') }}</span>
                                            @if($sLabel)
                                                <span class="text-[9px] font-bold {{ $sClass }} px-1.5 py-0.5 rounded">{{ $sLabel }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4" data-label="Match">
                                            <div class="flex items-center gap-2">
                                                <img src="{{ $fixture->home_team_logo }}" class="w-5 h-5 object-contain">
                                                <span class="text-sm font-bold text-slate-800 max-w-[100px] truncate">{{ $fixture->home_team }}</span>
                                                <span class="text-[10px] text-slate-300">vs</span>
                                                <span class="text-sm font-bold text-slate-800 max-w-[100px] truncate">{{ $fixture->away_team }}</span>
                                                <img src="{{ $fixture->away_team_logo }}" class="w-5 h-5 object-contain">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center" data-label="Score">
                                            @if(in_array($fixture->status, ['FT', 'AET', 'PEN']))
                                                @php $scoreWon = $prediction->status === 'won'; @endphp
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-xl font-black text-base {{ $scoreWon ? 'bg-green-600 text-white' : 'bg-slate-900 text-white' }}">
                                                    {{ $fixture->home_goals }} <span class="mx-0.5 text-xs opacity-60">–</span> {{ $fixture->away_goals }}
                                                </span>
                                            @elseif(in_array($fixture->status, ['LIVE','1H','HT','2H']))
                                                <span class="inline-flex flex-col items-center px-3 py-1.5 bg-red-500 text-white rounded-xl font-black">
                                                    <span class="flex items-center gap-1">
                                                        {{ $fixture->home_goals ?? 0 }} <span class="text-red-200 text-xs">–</span> {{ $fixture->away_goals ?? 0 }}
                                                    </span>
                                                    <span class="text-[9px] text-red-100 font-medium">{{ $fixture->elapsed ? $fixture->elapsed . "'" : 'LIVE' }}</span>
                                                </span>
                                            @else
                                                <span class="text-lg font-bold text-slate-300">––</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4" data-label="Tip">
                                            <span class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-extrabold text-sm shadow-sm group-hover:border-primary-500 group-hover:text-primary-600 transition-colors">
                                                {{ $prediction->tip }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-6 text-right pr-10" data-label="Odds">
                                            <span class="text-lg font-black text-slate-900">{{ $prediction->odds ?? '-' }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="bg-slate-50 rounded-[2.5rem] p-20 text-center border-2 border-dashed border-slate-200 max-w-3xl mx-auto">
                <i class="fas fa-calendar-times text-slate-300 text-5xl mb-6 block"></i>
                <h4 class="text-2xl font-bold text-slate-400">No Free Tips Scheduled</h4>
                <p class="text-slate-500 mt-2">Check back in a few hours for the next update.</p>
            </div>
        @endif
    </div>
</section>

<!-- Premium Tips Promo -->
<section class="mb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-emerald-500 via-primary-500 to-blue-500 rounded-[2.5rem] blur opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
            <div class="relative bg-slate-900 rounded-[2.5rem] overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-emerald-500/10 blur-[100px] rounded-full translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-blue-500/5 blur-[80px] rounded-full -translate-x-1/3 translate-y-1/3"></div>
                
                <div class="px-8 py-12 md:p-16 flex flex-col md:flex-row items-center justify-between gap-12">
                    <div class="max-w-2xl text-center md:text-left" data-aos="fade-right">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-black uppercase tracking-widest mb-6">
                            <i class="fas fa-certificate mr-2"></i> Guaranteed Quality
                        </div>
                        <h2 class="text-4xl lg:text-5xl font-black text-white mb-6 leading-tight">
                            ENJOY <span class="text-emerald-500">Esurebet</span> PREMIUM TIPS!
                        </h2>
                        <p class="text-lg text-slate-400 leading-relaxed font-medium">
                            Make maximum <span class="text-white font-bold">PROFITS</span> from our sure <span class="text-emerald-400 font-black">"2 to 5"</span> daily Football Predictions. Enjoy up to <span class="text-emerald-500 font-extrabold">95% winning</span> with our premium plan...
                        </p>
                    </div>
                    
                    <div class="flex-shrink-0" data-aos="zoom-in">
                        <a href="{{ route('pricing') }}" class="group/btn relative inline-flex items-center justify-center px-12 py-6 text-xl font-black text-white bg-emerald-600 rounded-2xl transition-all duration-300 hover:shadow-[0_0_40px_rgba(16,185,129,0.4)] hover:-translate-y-1 overflow-hidden">
                            <span class="relative z-10 flex items-center">
                                Access Now <i class="fas fa-bolt ml-4 group-hover/btn:animate-bounce"></i>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-400/0 via-white/20 to-emerald-400/0 -translate-x-full group-hover/btn:translate-x-full transition-transform duration-1000"></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sure Picks Section -->
<section class="py-24 bg-slate-50 relative overflow-hidden">
    <div class="absolute top-0 right-0 p-8 opacity-5">
        <i class="fas fa-futbol text-[200px] -rotate-12"></i>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12" data-aos="fade-up">
            <div class="max-w-2xl">
                <h2 class="text-sm font-black text-primary-600 uppercase tracking-[0.2em] mb-3">Guaranteed Insights</h2>
                <h3 class="text-4xl lg:text-5xl font-extrabold text-slate-900 mb-4">Sure Picks Tips</h3>
                <p class="text-lg text-slate-500">Hand-picked selections from our top analysts with the highest probability of winning today.</p>
            </div>
            <a href="{{ route('predictions') }}" class="mt-6 md:mt-0 inline-flex items-center text-primary-600 font-bold hover:text-primary-700 transition-colors">
                View All Sure Picks <i class="fas fa-arrow-right ml-2 text-sm"></i>
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-lg border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left responsive-table-green">
                    <thead>
                        <tr class="bg-green-50 border-b border-green-100">
                            <th class="px-6 py-4 text-[10px] font-black text-green-700 uppercase tracking-widest">Time</th>
                            <th class="px-6 py-4 text-[10px] font-black text-green-700 uppercase tracking-widest">Match</th>
                            <th class="px-6 py-4 text-[10px] font-black text-green-700 uppercase tracking-widest text-center">Score</th>
                            <th class="px-6 py-4 text-[10px] font-black text-green-700 uppercase tracking-widest">Tip</th>
                            <th class="px-6 py-4 text-[10px] font-black text-green-700 uppercase tracking-widest text-center">Confidence</th>
                            <th class="px-6 py-4 text-[10px] font-black text-green-700 uppercase tracking-widest text-right pr-6">Odds</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($surePicksTips as $tip)
                        @php $pred = $tip->predictions->first(); @endphp
                        <tr class="hover:bg-green-50/30 transition-colors cursor-pointer" onclick="window.location='{{ route('match.detail', $tip->id) }}'">
                            <td class="px-6 py-4" data-label="Time">
                                <span class="font-bold text-slate-900">{{ $tip->match_date->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4" data-label="Match">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $tip->home_team_logo }}" class="w-5 h-5 object-contain">
                                    <span class="text-sm font-bold text-slate-800">{{ $tip->home_team }}</span>
                                    <span class="text-[10px] text-slate-300">vs</span>
                                    <span class="text-sm font-bold text-slate-800">{{ $tip->away_team }}</span>
                                    <img src="{{ $tip->away_team_logo }}" class="w-5 h-5 object-contain">
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center" data-label="Score">
                                @if(in_array($tip->status, ['FT','AET','PEN']))
                                    @php $spPred = $tip->predictions->first(); $scoreWon = $spPred && $spPred->status === 'won'; @endphp
                                    <span class="font-black px-3 py-1.5 rounded-lg {{ $scoreWon ? 'bg-green-600 text-white' : 'bg-slate-900 text-white' }}">{{ $tip->home_goals }} – {{ $tip->away_goals }}</span>
                                @else
                                    <span class="text-slate-300 font-bold">––</span>
                                @endif
                            </td>
                            <td class="px-6 py-4" data-label="Tip">
                                <span class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-extrabold text-sm shadow-sm group-hover:border-primary-500 group-hover:text-primary-600 transition-colors">
                                                
                                    {{ $pred->tip ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center" data-label="Confidence">
                                                <span class="font-bold text-green-600" style="color:#000 !important">{{ $pred->confidence ?? '-' }}%</span>
                            </td>
                            <td class="px-6 py-4 text-right pr-6" data-label="Odds">
                                <span class="font-black text-slate-900">{{ $pred->odds ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="fas fa-search text-3xl mb-3 block opacity-30"></i>
                                No sure picks yet. Check back shortly!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Results Timeline (Horizontal) -->
<section class="py-12 bg-slate-900 border-y border-white/5 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8 flex items-center justify-between">
        <h3 class="text-xl font-bold text-white flex items-center">
            <i class="fas fa-history mr-3 text-primary-500"></i> Recent Premium Results

        </h3>
        <div class="flex items-center space-x-2">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            <span class="text-xs font-bold text-slate-500 uppercase">95% ACCURACY RATE</span>
        </div>
    </div>
    
    <div class="relative group">
        <div class="flex space-x-4 overflow-x-auto pb-6 px-4 no-scrollbar">
            @foreach(array_merge($vipResults->all(), $vvipResults->all()) as $result)
            <div class="min-w-[140px] bg-slate-800/50 backdrop-blur-sm border border-white/5 rounded-2xl p-4 text-center group/card transition-all duration-300 hover:bg-slate-800 hover:border-white/10">
                <div class="text-[10px] font-bold text-slate-500 mb-2">{{ $result->date->format('d M') }}</div>
                <div class="text-lg font-black text-white mb-2">{{ $result->odds }}<span class="text-xs text-slate-500 ml-0.5">Odds</span></div>
                <div class="flex justify-center">
                    <div class="w-8 h-8 rounded-full {{ $result->status === 'win' ? 'bg-primary-500/20 text-primary-500' : 'bg-red-500/20 text-red-500' }} flex items-center justify-center shadow-lg {{ $result->status === 'win' ? 'shadow-primary-500/20' : 'shadow-red-500/20' }}">
                        @if($result->status === 'win')
                            <i class="fas fa-check text-xs"></i>
                        @else
                            <i class="fas fa-times text-xs"></i>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <!-- Gradient fades for list -->
        <div class="absolute inset-y-0 left-0 w-20 bg-gradient-to-r from-slate-900 to-transparent pointer-events-none"></div>
        <div class="absolute inset-y-0 right-0 w-20 bg-gradient-to-l from-slate-900 to-transparent pointer-events-none"></div>
    </div>
</section>



<!-- Featured Predictions Table -->
@if($featuredByLeague->count() > 0)
<section class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-sm font-black text-primary-600 uppercase tracking-[0.2em] mb-3">Top Markets</h2>
            <h3 class="text-4xl lg:text-5xl font-extrabold text-slate-900 mb-4">Most Featured Selections</h3>
            <p class="text-lg text-slate-500 max-w-2xl mx-auto">The most anticipated upcoming matches with in-depth analysis and expert betting tips.</p>
        </div>

        <div class="space-y-10">
            @foreach($featuredByLeague as $leagueName => $fixtures)
            <div data-aos="fade-up">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-1.5 h-6 bg-primary-500 rounded-full"></span>
                    <h4 class="text-xl font-black text-slate-900">{{ $leagueName }}
                        <span class="ml-3 text-[10px] font-black text-slate-400 border border-slate-200 px-2 py-0.5 rounded-md uppercase tracking-widest">{{ $fixtures[0]['league_country'] }}</span>
                    </h4>
                </div>
                
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left responsive-table">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Time</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Match</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Score</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tip</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Confidence</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right pr-6">Odds</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($fixtures as $fixture)
                                @foreach($fixture->predictions as $prediction)
                                <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="window.location='{{ route('match.detail', $fixture->id) }}'">
                                    <td class="px-6 py-4" data-label="Time">
                                        <span class="font-bold text-slate-900">{{ $fixture->match_date->format('H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-4" data-label="Match">
                                        <div class="flex items-center gap-2">
                                            <img src="{{ $fixture->home_team_logo }}" class="w-5 h-5 object-contain">
                                            <span class="text-sm font-bold text-slate-800">{{ $fixture->home_team }}</span>
                                            <span class="text-[10px] text-slate-300">vs</span>
                                            <span class="text-sm font-bold text-slate-800">{{ $fixture->away_team }}</span>
                                            <img src="{{ $fixture->away_team_logo }}" class="w-5 h-5 object-contain">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center" data-label="Score">
                                        @if(in_array($fixture->status, ['FT','AET','PEN']))
                                            @php $scoreWon = $prediction->status === 'won'; @endphp
                                            <span class="font-black px-3 py-1.5 rounded-lg {{ $scoreWon ? 'bg-green-600 text-white' : 'bg-slate-900 text-white' }}">{{ $fixture->home_goals }} – {{ $fixture->away_goals }}</span>
                                        @else
                                            <span class="text-slate-300 font-bold">––</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4" data-label="Tip">
                                        <span class="px-3 py-1.5 bg-primary-50 text-primary-700 rounded-xl text-sm font-bold">
                                            {{ $prediction->tip }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4" data-label="Confidence">
                                        <span class="font-bold text-primary-600">{{ $prediction->confidence }}%</span>
                                    </td>
                                    <td class="px-6 py-4 text-right pr-6" data-label="Odds">
                                        <span class="font-black text-slate-900">{{ $prediction->odds ?? '-' }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        </div>
    </div>
</section>
@endif

<!-- Basketball Tips Section -->
<section class="py-24 bg-gradient-to-br from-orange-50 to-red-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12" data-aos="fade-up">
            <div>
                <h2 class="text-sm font-black text-orange-600 uppercase tracking-[0.2em] mb-3">Multi-Sport</h2>
                <h3 class="text-4xl lg:text-5xl font-extrabold text-slate-900 mb-4">🏀 Basketball Predictions</h3>
                <p class="text-lg text-slate-500">Expert NBA & top league predictions with money line, spread, and totals.</p>
            </div>
            <a href="{{ route('basketball') }}" class="mt-6 md:mt-0 inline-flex items-center text-orange-600 font-bold hover:text-orange-700 transition-colors">
                View All Basketball <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        @if(isset($basketballTips) && $basketballTips->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($basketballTips->take(3) as $tip)
            <div class="bg-white rounded-2xl shadow-lg border border-orange-200 overflow-hidden hover:shadow-xl transition-all duration-300" data-aos="fade-up">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 px-5 py-3 flex items-center justify-between">
                    <span class="text-white text-xs font-bold">{{ $tip->league_name }}</span>
                    <span class="text-orange-100 text-xs">{{ $tip->match_date->format('M d, H:i') }}</span>
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-center flex-1">
                            <span class="font-bold text-slate-800 text-sm block">{{ $tip->home_team }}</span>
                        </div>
                        <span class="text-lg font-black text-slate-300 mx-3">VS</span>
                        <div class="text-center flex-1">
                            <span class="font-bold text-slate-800 text-sm block">{{ $tip->away_team }}</span>
                        </div>
                    </div>
                    @if($tip->predictions->isNotEmpty())
                        @php $pred = $tip->predictions->first(); @endphp
                        <div class="bg-orange-50 rounded-xl p-3 mt-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Pick:</span>
                                <span class="font-bold text-orange-700">{{ $pred->tip }}</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-slate-600">Confidence:</span>
                                <span class="font-bold text-green-600">{{ $pred->confidence }}%</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12 bg-white/50 rounded-3xl border-2 border-dashed border-orange-200">
            <div class="w-20 h-20 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-basketball-ball text-orange-400 text-3xl"></i>
            </div>
            <h4 class="text-xl font-bold text-slate-600 mb-2">NBA & Basketball Predictions Coming Soon</h4>
            <p class="text-slate-500 max-w-md mx-auto">We're preparing expert basketball analysis. Check back soon for NBA, EuroLeague, and more!</p>
        </div>
        @endif
    </div>
</section>

{{-- Blog Section --}}
@if($blogPosts->count() > 0)
<section class="py-24 bg-white relative overflow-hidden">
    <div class="absolute top-0 left-0 p-8 opacity-[0.03]">
        <i class="fas fa-newspaper text-[200px] -rotate-12"></i>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12" data-aos="fade-up">
            <div class="max-w-2xl">
                <h2 class="text-sm font-black text-primary-600 uppercase tracking-[0.2em] mb-3">Football News</h2>
                <h3 class="text-4xl lg:text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">Latest Soccer Updates</h3>
                <p class="text-lg text-slate-500">Stay informed with the latest football news, match previews, and expert analysis.</p>
            </div>
            <a href="{{ route('blog.category', 'soccer') }}" class="mt-6 md:mt-0 inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-2xl font-bold text-sm hover:bg-primary-700 transition-all shadow-lg hover:shadow-primary-600/20 group">
                More Articles <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($blogPosts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="group bg-white rounded-[2rem] border border-slate-100 overflow-hidden hover:shadow-xl hover:border-primary-500/30 transition-all duration-300" data-aos="fade-up">
                <div class="h-44 bg-slate-100 overflow-hidden relative">
                    @if($post->featured_image)
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100">
                            <i class="fas fa-futbol text-6xl text-primary-300/50"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                    <span class="absolute top-4 left-4 px-3 py-1 bg-primary-600/90 text-white text-[10px] font-black uppercase tracking-widest rounded-full backdrop-blur-sm">Soccer</span>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">
                        <i class="far fa-calendar-alt text-primary-500"></i>
                        <span>{{ $post->published_at->format('M d, Y') }}</span>
                    </div>
                    <h4 class="text-base font-black text-slate-900 group-hover:text-primary-600 transition-colors leading-snug line-clamp-2 mb-2">{{ $post->title }}</h4>
                    <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed">{{ $post->excerpt }}</p>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400">{{ $post->author }}</span>
                        <span class="text-xs font-bold text-primary-600 group-hover:translate-x-1 transition-transform">Read More <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Premium CTA Banner -->
<section class="py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative bg-slate-900 rounded-[3rem] overflow-hidden p-12 md:p-20 shadow-2xl shadow-primary-500/10">
            <!-- Background effects -->
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary-500/20 blur-[130px] rounded-full translate-x-1/3 -translate-y-1/3"></div>
            <div class="absolute inset-0 bg-[url('https://legitpredict.com/assets/images/pattern.png')] opacity-10"></div>
            
            <div class="relative grid md:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-up">
                    <h2 class="text-sm font-black text-primary-400 uppercase tracking-[0.3em] mb-4">Unleash Your Potential</h2>
                    <h3 class="text-4xl lg:text-5xl font-extrabold text-white mb-6 leading-tight">Ready to Master the Art of <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">Winning?</span></h3>
                    <p class="text-lg text-slate-300 mb-10 max-w-md">Join over 10,000 successful bettors and get instant access to our highest margin, 85%+ accuracy VIP tips.</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl hover:from-primary-600 hover:to-primary-700 transition-all duration-300 shadow-xl shadow-primary-500/20">
                            Create Account
                        </a>
                        <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-white border-2 border-white/10 rounded-2xl hover:bg-white/5 transition-all duration-300">
                            Pricing Plans
                        </a>
                    </div>
                </div>
                
                <div class="hidden md:grid grid-cols-2 gap-4" data-aos="zoom-in">
                    <div class="space-y-4">
                        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 text-center">
                            <i class="fas fa-gem text-3xl text-primary-400 mb-4"></i>
                            <div class="text-xl font-black text-white">VVIP</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Status</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 text-center transform translate-x-4">
                            <i class="fas fa-check-double text-3xl text-green-400 mb-4"></i>
                            <div class="text-xl font-black text-white">X2 Odds</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Sure Picks</div>
                        </div>
                    </div>
                    <div class="space-y-4 pt-8">
                        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 text-center">
                            <i class="fas fa-lock-open text-3xl text-blue-400 mb-4"></i>
                            <div class="text-xl font-black text-white">Instant</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Unlock</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 text-center transform translate-x-4">
                            <i class="fas fa-chart-line text-3xl text-purple-400 mb-4"></i>
                            <div class="text-xl font-black text-white">85%+</div>
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Accuracy</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SEO Content Section -->
<section class="py-24 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-3 gap-12">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 mb-6">Best Football Prediction Website for Winning Tips for Today</h2>
                    <p class="text-slate-600 leading-relaxed mb-4">
                        Welcome to <strong>Esurebet</strong>, your ultimate destination for highly <strong>accurate football prediction</strong> and expert betting insights. If you are searching for the <strong>best football prediction website</strong>, look no further. Our platform is built on advanced statistical modeling and deep analytical expertise, providing you with the <strong>winning tips for today</strong> that you need to stay ahead of the game.
                    </p>
                    <p class="text-slate-600 leading-relaxed mb-4">
                        Whether you are looking for <strong>sure tips prediction</strong>, daily <strong>Over/Under predictions</strong>, or the <strong>best betting tip</strong> for the weekend, our team of seasoned analysts works around the clock to deliver winning forecasts. We cover all major European leagues including the English Premier League, Spanish La Liga, Italian Serie A, and the UEFA Champions League, alongside numerous continental and local divisions with <strong>football prediction for tomorrow</strong> always ready.
                    </p>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-slate-900 mb-4">Why Choose Our Expert Betting Tips?</h3>
                    <p class="text-slate-600 leading-relaxed mb-4">
                        At Esurebet, we don't just guess; we analyze. Every tip released on our platform undergoes a rigorous verification process. We look at team form, player injuries, head-to-head statistics, and even psychological factors to ensure our <strong>85%+ win rate</strong> remains consistent. Our <strong>Sure Picks</strong> are hand-selected choices geared towards minimizing risk and maximizing profit.
                    </p>
                    <p class="text-slate-600 leading-relaxed">
                        For those seeking a more exclusive experience, our <strong>VIP and VVIP plans</strong> offer premium access to "2 to 5" daily odds, max odds selections, and personalized betting strategies. Our goal is to empower bettors with professional insights that turn casual betting into a disciplined, profitable venture.
                    </p>
                </div>
            </div>

            <!-- Side Highlights -->
            <div class="space-y-8">
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
                    <h4 class="text-sm font-black text-primary-600 uppercase tracking-widest mb-6">Market Coverage</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-emerald-500 mt-1 mr-3"></i>
                            <span class="text-sm text-slate-700 font-medium">Accumulator & Multiples Tips</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-emerald-500 mt-1 mr-3"></i>
                            <span class="text-sm text-slate-700 font-medium">Daily Sure 2 Odds Selections</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-emerald-500 mt-1 mr-3"></i>
                            <span class="text-sm text-slate-700 font-medium">HT/FT & Correct Score Markets</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-emerald-500 mt-1 mr-3"></i>
                            <span class="text-sm text-slate-700 font-medium">Draws & Double Chance Betting</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-emerald-500 mt-1 mr-3"></i>
                            <span class="text-sm text-slate-700 font-medium">Goal Markets (Global Coverage)</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-primary-600 p-8 rounded-3xl text-white shadow-lg shadow-primary-500/20">
                    <h4 class="text-xl font-black mb-4">Start Winning Today</h4>
                    <p class="text-primary-100 text-sm mb-6 leading-relaxed">
                        Join over 10,000 active users who rely on our professional football insights to beat the bookies.
                    </p>
                    <a href="{{ route('register') }}" class="inline-flex items-center text-white font-black text-sm uppercase tracking-widest group">
                        Join Now <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection