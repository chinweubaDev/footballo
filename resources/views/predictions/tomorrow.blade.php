@extends('layouts.app')

@section('title', 'Football Predictions for Today - Sure Tips Prediction')
@section('meta_description', 'Get the most accurate football predictions for today. Our sure tips prediction and expert analysis help you win big every day.')
@section('meta_keywords', 'football predictions today, sure tips prediction, bet tips, accurate football prediction')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    <!-- Hero/Header Section -->
    <section class="relative bg-slate-900 pt-32 pb-20 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #22c55e 1px, transparent 0); background-size: 40px 40px;"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                <div data-aos="fade-right">
                    <h1 class="text-4xl lg:text-5xl font-black text-white mb-4">Football Predictions for Today</h1>
                    <p class="text-slate-400 text-lg max-w-xl">Get professional predictions for today's football action.</p>
                </div>
                
                <div class="flex items-center space-x-3 bg-white/5 border border-white/10 px-6 py-4 rounded-3xl" data-aos="fade-left">
                    <div class="w-10 h-10 bg-primary-500 rounded-2xl flex items-center justify-center text-white">
                        <i class="far fa-calendar-alt"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Date</span>
                        <span class="text-sm font-bold text-white">{{ today()->format('D, M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Predictions Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        @if($fixturesByLeague->count() > 0)
            @foreach($fixturesByLeague as $leagueName => $fixtures)
            <div class="mb-12" data-aos="fade-up">
                <div class="flex items-center space-x-4 mb-6 sticky top-24 z-20 bg-slate-50/80 backdrop-blur-md py-4 rounded-2xl px-4">
                    <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center">
                        @if($fixtures[0]->league_logo)
                            <img src="{{ $fixtures[0]->league_logo }}" alt="{{ $leagueName }}" class="w-8 h-8 object-contain">
                        @else
                            <i class="fas fa-trophy text-primary-500"></i>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                             <span class="text-[10px] font-black text-primary-600 uppercase tracking-widest">{{ $fixtures[0]->league_country ?? 'International' }}</span>
                             <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $fixtures->count() }} Matches</span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900">{{ $leagueName }}</h2>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($fixtures as $fixture)
                        @foreach($fixture->predictions as $prediction)
                        <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 group flex flex-col h-full ring-1 ring-slate-100">
                            <!-- Match Meta -->
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center">
                                    <i class="far fa-clock mr-1.5 text-primary-500"></i>
                                    {{ $fixture->match_date->format('H:i') }}
                                </span>
                                <span class="bg-slate-50 text-slate-600 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full border border-slate-100">
                                    {{ $prediction->category }}
                                </span>
                            </div>

                            <!-- Teams -->
                            <div class="space-y-4 mb-8">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center p-1 border border-slate-100">
                                            @if($fixture->home_team_logo)
                                                <img src="{{ $fixture->home_team_logo }}" class="w-full h-full object-contain">
                                            @else
                                                <i class="fas fa-shield-alt text-slate-300"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-bold text-slate-800">{{ $fixture->home_team }}</span>
                                    </div>
                                    <span class="text-xs font-black {{ in_array($fixture->status, ['FT','AET','PEN']) ? 'text-green-600' : 'text-slate-300' }}">
                                        @if(in_array($fixture->status, ['FT','AET','PEN'])) {{ $fixture->home_goals }} - {{ $fixture->away_goals }} @else -- @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center p-1 border border-slate-100">
                                            @if($fixture->away_team_logo)
                                                <img src="{{ $fixture->away_team_logo }}" class="w-full h-full object-contain">
                                            @else
                                                <i class="fas fa-shield-alt text-slate-300"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-bold text-slate-800">{{ $fixture->away_team }}</span>
                                    </div>
                                    <span class="text-xs font-black text-slate-300">{{ $fixture->match_date->format('H:i') }}</span>
                                </div>
                            </div>

                            <!-- Odds Display & Tip -->
                            <div class="mt-auto">
                                <div class="bg-slate-50 rounded-2xl p-4 mb-4 ring-1 ring-slate-100">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Recommended Tip</span>
                                        <span class="font-black text-primary-600 text-lg">{{ $prediction->tip }}</span>
                                    </div>
                                    <div class="border-t border-slate-200/50 pt-3 flex justify-between">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Odds</span>
                                        <span class="text-xs font-bold text-slate-900">{{ $prediction->odds ?? '-' }}</span>
                                    </div>
                                </div>

                                <!-- Confidence Bar -->
                                <div class="mb-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Confidence</span>
                                        <span class="text-xs font-black text-slate-900">{{ $prediction->confidence }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 p-0.5 shadow-inner">
                                        <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-0.5 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $prediction->confidence }}%"></div>
                                    </div>
                                </div>

                                <!-- Analysis (Truncated) -->
                                @if($prediction->analysis)
                                <p class="text-[11px] text-slate-500 italic mb-6 line-clamp-2 px-2 border-l-2 border-primary-500/20">
                                    "{{ $prediction->analysis }}"
                                </p>
                                @endif

                                <!-- Footer Badges -->
                                <div class="flex flex-wrap gap-2 pt-4 border-t border-slate-100">
                                    @if($prediction->is_premium)
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-crown mr-1"></i>Premium
                                        </span>
                                    @endif
                                    @if($prediction->is_maxodds)
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-purple-100 text-purple-800">
                                            <i class="fas fa-star mr-1"></i>Maxodds
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
            @endforeach
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-[3rem] p-20 text-center shadow-xl border border-slate-200">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-calendar-day text-slate-300 text-4xl animate-pulse"></i>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-4">No Tips Available Today</h2>
                <p class="text-slate-500 mb-8 max-w-md mx-auto">Our analysts are working on today's predictions. Please check back shortly.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white bg-primary-600 rounded-2xl hover:bg-primary-700 transition-all">
                    Back to Homepage
                </a>
            </div>
        @endif

        <!-- CTA Section -->
        <div class="relative bg-slate-900 rounded-[3rem] overflow-hidden p-12 mt-20 group">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-600/20 to-blue-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            <div class="relative text-center max-w-2xl mx-auto">
                <h2 class="text-3xl lg:text-4xl font-black text-white mb-6">Want 95%+ Accuracy?</h2>
                <p class="text-slate-400 mb-10 text-lg">Unlock our exclusive VIP algorithms and get access to the highest margin predictions in the market.</p>
                <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-white bg-primary-600 rounded-2xl hover:bg-primary-700 shadow-xl shadow-primary-600/20 transition-all hover:-translate-y-1">
                    Upgrade to VIP Access
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
