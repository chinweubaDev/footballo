@extends('layouts.app')

@section('title', $category . ' Predictions')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    <!-- Category Hero -->
    <section class="relative bg-slate-900 pt-32 pb-32 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #3b82f6 1px, transparent 0); background-size: 30px 30px;"></div>
        </div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-600/20 blur-[100px] rounded-full"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="zoom-out">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-500/10 border border-blue-500/20 rounded-3xl mb-8 text-blue-400">
                @php
                    $icon = match($category) {
                        'Over 1.5' => 'fa-arrow-up',
                        'Over 2.5' => 'fa-chevron-up',
                        'Double Chance' => 'fa-exchange-alt',
                        'Both Teams to Score' => 'fa-futbol',
                        'Draw' => 'fa-equals',
                        default => 'fa-chart-line'
                    };
                @endphp
                <i class="fas {{ $icon }} text-3xl"></i>
            </div>
            <h1 class="text-4xl lg:text-6xl font-black text-white mb-6">{{ $category }} Predictions</h1>
            <p class="text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Expert insights and mathematical models tailored specifically for the {{ $category }} betting market.
            </p>
        </div>
    </section>

    <!-- Category Switcher (Sticky Navigation) -->
    <div class="sticky top-20 z-40 bg-white/80 backdrop-blur-xl border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-1 overflow-x-auto py-4 no-scrollbar">
                <a href="{{ route('predictions') }}" class="px-5 py-2.5 rounded-2xl text-sm font-bold transition-all text-slate-500 hover:bg-slate-50 whitespace-nowrap">
                    All Markets
                </a>
                <span class="w-px h-4 bg-slate-200 mx-2"></span>
                @php
                    $categories = [
                        ['route' => 'predictions.over15', 'label' => 'Over 1.5', 'val' => 'Over 1.5'],
                        ['route' => 'predictions.over25', 'label' => 'Over 2.5', 'val' => 'Over 2.5'],
                        ['route' => 'predictions.double-chance', 'label' => 'Double Chance', 'val' => 'Double Chance'],
                        ['route' => 'predictions.bts', 'label' => 'BTS', 'val' => 'Both Teams to Score'],
                        ['route' => 'predictions.draw', 'label' => 'Draw', 'val' => 'Draw'],
                    ];
                @endphp
                @foreach($categories as $cat)
                    <a href="{{ route($cat['route']) }}" class="px-5 py-2.5 rounded-2xl text-sm font-bold transition-all whitespace-nowrap {{ $category == $cat['val'] ? 'bg-primary-600 text-white shadow-lg shadow-primary-600/20' : 'text-slate-600 hover:bg-slate-50' }}">
                        {{ $cat['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Predictions List -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
        @if($fixturesByLeague->count() > 0)
            @foreach($fixturesByLeague as $leagueName => $fixtures)
            <div class="mb-12" data-aos="fade-up">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-10 h-10 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center">
                        <img src="{{ $fixtures[0]->league_logo }}" class="w-6 h-6 object-contain opacity-80">
                    </div>
                    <div>
                        <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">{{ $fixtures[0]->league_country }}</span>
                        <h2 class="text-xl font-black text-slate-900 leading-tight">{{ $leagueName }}</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($fixtures as $fixture)
                        @foreach($fixture->predictions as $prediction)
                        <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 group ring-1 ring-slate-100 flex flex-col h-full">
                            <!-- Match Time/Meta -->
                            <div class="flex justify-between items-center mb-8 pb-4 border-b border-slate-50">
                                <span class="bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg">
                                    {{ $prediction->category }}
                                </span>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center">
                                    <i class="far fa-clock mr-1.5 text-blue-500"></i>
                                    {{ $fixture->match_date->format('H:i') }}
                                </span>
                            </div>

                            <!-- Match Content -->
                            <div class="flex items-center justify-between space-x-4 mb-8">
                                <div class="flex-1 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center p-2 mb-3 mx-auto border border-slate-100 group-hover:bg-white transition-colors shadow-inner">
                                        <img src="{{ $fixture->home_team_logo }}" class="w-full h-full object-contain">
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-800 line-clamp-1">{{ $fixture->home_team }}</span>
                                </div>
                                <div class="px-2 text-[10px] font-black text-slate-200">VS</div>
                                <div class="flex-1 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center p-2 mb-3 mx-auto border border-slate-100 group-hover:bg-white transition-colors shadow-inner">
                                        <img src="{{ $fixture->away_team_logo }}" class="w-full h-full object-contain">
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-800 line-clamp-1">{{ $fixture->away_team }}</span>
                                </div>
                            </div>

                            <!-- Prediction Footer -->
                            <div class="mt-auto">
                                <div class="bg-slate-900 rounded-3xl p-5 mb-6 text-center transform transition-all group-hover:scale-105 duration-300 shadow-xl shadow-slate-900/10">
                                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] block mb-2">Expert Tip</span>
                                    <span class="text-2xl font-black text-white italic">"{{ $prediction->tip }}"</span>
                                </div>

                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Confidence Level</span>
                                    <span class="text-xs font-black text-blue-600">{{ $prediction->confidence }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 p-0.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-0.5 rounded-full transition-all duration-700 shadow-sm" style="width: {{ $prediction->confidence }}%"></div>
                                </div>
                                
                                <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="text-lg font-black text-slate-900">{{ $prediction->odds ?? '1.85' }}</div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1">Odds</div>
                                    </div>
                                    <button class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
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
            <div class="bg-white rounded-[4rem] p-24 text-center shadow-2xl border border-slate-100 max-w-4xl mx-auto">
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-search-dollar text-blue-400 text-3xl"></i>
                </div>
                <h3 class="text-3xl font-black text-slate-900 mb-4">No {{ $category }} Tips Yet</h3>
                <p class="text-slate-500 text-lg mb-10">Our analysts are scouring the markets for the best {{ $category }} value today. New selections appear here as soon as they're confirmed.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                     <a href="{{ route('predictions') }}" class="px-8 py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition-all">
                        View All Markets
                     </a>
                     <a href="{{ route('home') }}" class="px-8 py-4 bg-white border border-slate-200 text-slate-600 font-bold rounded-2xl hover:bg-slate-50 transition-all">
                        Homepage
                     </a>
                </div>
            </div>
        @endif
    </div>

    <!-- CTA Section -->
    <section class="mt-32 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-[3.5rem] p-12 lg:p-20 text-white text-center shadow-2xl shadow-blue-500/30 overflow-hidden relative">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
            <div class="relative z-10" data-aos="fade-up">
                <h2 class="text-3xl lg:text-5xl font-black mb-6">Master the {{ $category }} Market</h2>
                <p class="text-xl text-blue-100 mb-12 max-w-2xl mx-auto">Unlock access to our high-margin algorithm that predicts {{ $category }} outcomes with up to 95% accuracy.</p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center">
                    <a href="{{ route('premium.tips') }}" class="bg-white text-blue-600 px-10 py-5 rounded-2xl font-black text-lg hover:shadow-xl hover:-translate-y-1 transition-all">
                        <i class="fas fa-crown mr-3"></i> Get VIP Tips
                    </a>
                    <a href="{{ route('pricing') }}" class="bg-blue-500/50 backdrop-blur-md text-white border border-white/20 px-10 py-5 rounded-2xl font-black text-lg hover:bg-blue-500 transition-all">
                        <i class="fas fa-credit-card mr-3"></i> Premium Plans
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

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
