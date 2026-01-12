@extends('layouts.app')

@section('title', 'Expert Football Predictions & Winning Tips')
@section('meta_description', 'Get accurate football predictions, expert analysis, and winning daily tips. Join thousands of successful bettors with our professional insights.')

@section('content')
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
                    Win Big with <br>
                    <span class="bg-gradient-to-r from-primary-400 via-primary-500 to-primary-600 bg-clip-text text-transparent">Professional</span> Insights
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
                    <div class="flex -space-x-3">
                        @for($i = 1; $i <= 5; $i++)
                            <img class="w-10 h-10 rounded-full border-2 border-slate-900 bg-slate-800" src="https://ui-avatars.com/api/?name=User+{{$i}}&background=random" alt="User {{$i}}">
                        @endfor
                        <div class="w-10 h-10 rounded-full border-2 border-slate-900 bg-primary-600 flex items-center justify-center text-xs font-bold text-white uppercase tracking-tighter">
                            10k+
                        </div>
                    </div>
                    <div class="text-slate-400 text-sm">
                        <div class="flex items-center text-yellow-400 mb-0.5">
                            @for($i = 0; $i < 5; $i++) <i class="fas fa-star text-xs"></i> @endfor
                        </div>
                        Trusted by 10,000+ active users
                    </div>
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
                <div class="text-3xl font-black text-slate-900 mb-1">85%</div>
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

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($surePicksTips as $tip)
            <div class="bg-white rounded-[2rem] p-1 shadow-xl shadow-slate-200/60 border border-slate-100 group transition-all duration-500 hover:scale-[1.02]" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="bg-slate-50 rounded-[1.8rem] p-6 h-full">
                    <!-- Match Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex flex-col items-center flex-1 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center p-2 mb-2">
                                @if($tip->home_team_logo)
                                    <img src="{{ $tip->home_team_logo }}" alt="{{ $tip->home_team }}" class="w-full h-full object-contain">
                                @else
                                    <i class="fas fa-shield-alt text-slate-300"></i>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-slate-900 truncate w-full">{{ $tip->home_team }}</span>
                        </div>
                        <div class="px-4 text-xs font-black text-slate-300">VS</div>
                        <div class="flex flex-col items-center flex-1 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center p-2 mb-2">
                                @if($tip->away_team_logo)
                                    <img src="{{ $tip->away_team_logo }}" alt="{{ $tip->away_team }}" class="w-full h-full object-contain">
                                @else
                                    <i class="fas fa-shield-alt text-slate-300"></i>
                                @endif
                            </div>
                            <span class="text-xs font-bold text-slate-900 truncate w-full">{{ $tip->away_team }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-200/50 pb-4">
                        <span class="flex items-center"><i class="far fa-calendar-alt mr-1.5"></i> {{ $tip->match_date->format('M d, Y') }}</span>
                        <span class="flex items-center"><i class="far fa-clock mr-1.5"></i> {{ $tip->match_time ? $tip->match_time->format('H:i') : '-' }}</span>
                    </div>

                    <!-- Prediction -->
                    <div class="space-y-4 mb-6">
                        <div class="bg-white rounded-2xl p-4 flex justify-between items-center shadow-sm ring-1 ring-slate-100">
                            <div>
                                <span class="text-[10px] font-bold text-primary-500 uppercase tracking-widest block mb-1">Prediction</span>
                                <span class="font-extrabold text-slate-900">{{ $tip->prediction }}</span>
                            </div>
                            @if($tip->odds)
                                <div class="text-right">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Odds</span>
                                    <span class="font-black text-primary-600 text-lg">{{ number_format($tip->odds, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary-100 text-primary-700">
                            <i class="fas fa-check-circle mr-1.5"></i> High Confidence
                        </span>
                        <div class="flex space-x-2">
                            <button class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 hover:bg-primary-500 hover:text-white transition-all duration-300">
                                <i class="fas fa-share-alt text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-[2rem] p-12 text-center border-2 border-dashed border-slate-200">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-search text-slate-300 text-3xl"></i>
                </div>
                <h4 class="text-2xl font-bold text-slate-400">No Sure Picks Yet</h4>
                <p class="text-slate-400 mt-2">Checking for today's high-confidence tips. Check back shortly!</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Results Timeline (Horizontal) -->
<section class="py-12 bg-slate-900 border-y border-white/5 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8 flex items-center justify-between">
        <h3 class="text-xl font-bold text-white flex items-center">
            <i class="fas fa-history mr-3 text-primary-500"></i> Recent Win History
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

<!-- Free Tips Table -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h2 class="text-sm font-black text-primary-600 uppercase tracking-[0.2em] mb-3">Daily Forecasts</h2>
            <h3 class="text-4xl lg:text-5xl font-extrabold text-slate-900 mb-4">Free Tips of the Day</h3>
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
                        <table class="w-full text-left">
                            <thead class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <tr>
                                    <th class="px-6 py-5">Time</th>
                                    <th class="px-6 py-5">Matchup</th>
                                    <th class="px-6 py-5">Expert Tip</th>
                                    <th class="px-6 py-5 text-right pr-10">Odds</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/50">
                                @foreach($fixtures as $fixture)
                                    @foreach($fixture->predictions as $prediction)
                                    <tr class="hover:bg-white transition-colors group cursor-pointer" onclick="window.location.href='#'">
                                        <td class="px-6 py-6 border-l-4 border-transparent group-hover:border-primary-500 transition-all">
                                            <span class="font-bold text-slate-900 block">{{ $fixture->match_date->format('H:i') }}</span>
                                            <span class="text-[10px] text-slate-400 uppercase font-black uppercase tracking-widest">Today</span>
                                        </td>
                                        <td class="px-6 py-6">
                                            <div class="flex items-center space-x-8">
                                                <div class="flex items-center space-x-3 w-32 justify-end">
                                                    <span class="text-sm font-bold text-slate-700 truncate">{{ $fixture->home_team }}</span>
                                                    <img src="{{ $fixture->home_team_logo }}" class="w-6 h-6 object-contain grayscale group-hover:grayscale-0 transition-all">
                                                </div>
                                                <span class="text-[10px] font-black text-slate-300">VS</span>
                                                <div class="flex items-center space-x-3 w-32">
                                                    <img src="{{ $fixture->away_team_logo }}" class="w-6 h-6 object-contain grayscale group-hover:grayscale-0 transition-all">
                                                    <span class="text-sm font-bold text-slate-700 truncate">{{ $fixture->away_team }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-6">
                                            <span class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-900 font-extrabold text-sm shadow-sm group-hover:border-primary-500 group-hover:text-primary-600 transition-colors">
                                                {{ $prediction->tip }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-6 text-right pr-10">
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
            <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="w-1.5 h-6 bg-primary-500 rounded-full"></span>
                    <h4 class="text-xl font-black text-slate-900 flex items-center">
                        {{ $leagueName }}
                        <span class="ml-3 text-[10px] font-black text-slate-400 border border-slate-200 px-2 py-0.5 rounded-md uppercase tracking-widest">{{ $fixtures[0]['league_country'] }}</span>
                    </h4>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($fixtures as $fixture)
                        @foreach($fixture->predictions as $prediction)
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 ring-1 ring-slate-100 group">
                            <div class="flex justify-between items-start mb-6">
                                <span class="bg-primary-50 text-primary-600 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full">MATCH PICK</span>
                                <span class="text-xs font-bold text-slate-400">{{ $fixture->match_date->format('M d, H:i') }}</span>
                            </div>
                            
                            <div class="space-y-3 mb-8">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $fixture->home_team_logo }}" class="w-6 h-6 object-contain opacity-50 group-hover:opacity-100 transition-opacity">
                                        <span class="text-sm font-bold text-slate-700">{{ $fixture->home_team }}</span>
                                    </div>
                                    <span class="text-lg font-black text-slate-300">1</span>
                                </div>
                                <div class="flex items-center justify-between line-through opacity-10 py-1">
                                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">Draw</span>
                                    <span class="text-xs font-black text-slate-200">X</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $fixture->away_team_logo }}" class="w-6 h-6 object-contain opacity-50 group-hover:opacity-100 transition-opacity">
                                        <span class="text-sm font-bold text-slate-700">{{ $fixture->away_team }}</span>
                                    </div>
                                    <span class="text-lg font-black text-slate-300">2</span>
                                </div>
                            </div>
                            
                            <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-0.5">Recommended Tip</span>
                                    <span class="font-black text-slate-900 underline decoration-primary-500/30 decoration-4 underline-offset-4">{{ $prediction->tip }}</span>
                                </div>
                                <div class="bg-slate-900 text-white w-10 h-10 rounded-2xl flex items-center justify-center shadow-lg shadow-slate-900/20 group-hover:bg-primary-500 group-hover:shadow-primary-500/20 transition-all">
                                    <i class="fas fa-plus text-xs"></i>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
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