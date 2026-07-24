@extends('layouts.app')

@section('title', 'Sure Tips Prediction - ' . $category . ' Winning Tips for Today')
@section('meta_description', 'Get the best ' . $category . ' sure tips prediction and winning tips for today. Our accurate football prediction models cover all major leagues.')
@section('meta_keywords', $category . ' predictions, winning tips for today, sure tips prediction, best betting tip')

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
            <h1 class="text-4xl lg:text-6xl font-black text-white mb-6">{{ $category }} Sure Tips Prediction</h1>
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

                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left responsive-table">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Time</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Match</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Score</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tip</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Conf</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right pr-6">Odds</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                    @foreach($fixtures as $fixture)
                        @foreach($fixture->predictions as $prediction)
                        @php
                            $tipField = match($category) {
                                'Over 1.5' => 'over15_tip_content',
                                'Over 2.5' => 'over25_tip_content',
                                'Double Chance' => 'double_chance_tip_content',
                                'Both Teams to Score' => 'bts_tip_content',
                                'Draw' => 'draw_tip_content',
                                default => 'tip'
                            };
                            $tip = $prediction->{$tipField} ?? $prediction->tip;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="window.location='{{ route('match.detail', $fixture->id) }}'">
                            <td class="px-6 py-4" data-label="Time"><span class="font-bold text-slate-900">{{ $fixture->match_date->format('H:i') }}</span></td>
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
                                @elseif(in_array($fixture->status, ['LIVE','1H','2H','HT','ET','BT']))
                                    <span class="font-black bg-red-100 text-red-700 px-2 py-1 rounded-lg">{{ $fixture->home_goals ?? 0 }} – {{ $fixture->away_goals ?? 0 }}</span>
                                @else
                                    <span class="text-slate-300 font-bold">––</span>
                                @endif
                            </td>
                            <td class="px-6 py-4" data-label="Tip"><span class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-xl text-sm font-bold">{{ $tip }}</span></td>
                            <td class="px-6 py-4" data-label="Conf"><span class="font-bold text-blue-600">{{ $prediction->confidence }}%</span></td>
                            <td class="px-6 py-4 text-right pr-6" data-label="Odds"><span class="font-black text-slate-900">{{ $prediction->odds ?? '-' }}</span></td>
                        </tr>
                        @endforeach
                    @endforeach
                        </tbody>
                    </table>
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
                    <a href="{{ route('predictions.premium') }}" class="bg-white text-blue-600 px-10 py-5 rounded-2xl font-black text-lg hover:shadow-xl hover:-translate-y-1 transition-all">
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
