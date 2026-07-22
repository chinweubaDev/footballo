@extends('layouts.app')

@section('title', 'Basketball Predictions - NBA & Top Leagues Tips')
@section('meta_description', 'Expert basketball predictions for NBA and top leagues. Money line, spread, and total points analysis.')

@section('content')
<!-- Basketball Hero -->
<section class="relative bg-gradient-to-br from-orange-600 via-orange-700 to-red-700 py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #fff 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-[500px] h-[500px] bg-orange-400/20 blur-[120px] rounded-full"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-white/10 border border-white/20 rounded-3xl mb-8 text-white">
            <i class="fas fa-basketball-ball text-3xl"></i>
        </div>
        <h1 class="text-4xl lg:text-6xl font-black text-white mb-6">🏀 Basketball Predictions</h1>
        <p class="text-xl text-orange-100 max-w-2xl mx-auto leading-relaxed">
            Expert NBA and top basketball league predictions with money line, spread, and total points analysis.
        </p>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($basketballTips->count() > 0)
        @foreach($fixturesByLeague as $leagueName => $tips)
        <div class="mb-16" data-aos="fade-up">
            <div class="flex items-center space-x-4 mb-8">
                <div class="w-10 h-10 bg-orange-100 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-basketball-ball text-orange-600"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-900">{{ $leagueName }}</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tips as $tip)
                <div class="bg-white rounded-2xl shadow-lg border border-orange-200 overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 px-5 py-3 flex items-center justify-between">
                        <span class="text-white text-xs font-bold bg-white/20 px-2 py-0.5 rounded-full">{{ $tip->league_name }}</span>
                        <span class="text-orange-100 text-xs font-medium">{{ $tip->match_date->format('M d, H:i') }}</span>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-center flex-1">
                                <img src="{{ $tip->home_team_logo }}" class="w-14 h-14 object-contain mx-auto mb-2" alt="{{ $tip->home_team }}">
                                <span class="font-bold text-slate-800 text-sm">{{ $tip->home_team }}</span>
                            </div>
                            
                            @if($tip->status === 'FT' || $tip->status === 'AET')
                            <div class="text-center px-4">
                                <span class="text-2xl font-black text-slate-900">{{ $tip->home_goals }} - {{ $tip->away_goals }}</span>
                                <span class="block text-xs text-green-600 font-bold mt-1">FINAL</span>
                            </div>
                            @else
                            <span class="text-lg font-black text-slate-300 mx-4">VS</span>
                            @endif
                            
                            <div class="text-center flex-1">
                                <img src="{{ $tip->away_team_logo }}" class="w-14 h-14 object-contain mx-auto mb-2" alt="{{ $tip->away_team }}">
                                <span class="font-bold text-slate-800 text-sm">{{ $tip->away_team }}</span>
                            </div>
                        </div>

                        @if($tip->predictions->isNotEmpty())
                            @php $pred = $tip->predictions->first(); @endphp
                            <div class="bg-orange-50 rounded-xl p-4 space-y-2 mt-4">
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Prediction</span>
                                    <span class="font-bold text-orange-700">{{ $pred->tip }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Confidence</span>
                                    <span class="font-bold text-green-600">{{ $pred->confidence }}%</span>
                                </div>
                                @if($pred->odds)
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Odds</span>
                                    <span class="font-bold text-slate-900">{{ number_format($pred->odds, 2) }}</span>
                                </div>
                                @endif
                                @if($pred->analysis)
                                <p class="text-sm text-slate-600 mt-2 leading-relaxed">{{ Str::limit($pred->analysis, 200) }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        
        <div class="mt-12">
            {{ $basketballTips->links() }}
        </div>
    @else
        <div class="text-center py-20">
            <div class="w-24 h-24 bg-orange-100 rounded-3xl flex items-center justify-center mx-auto mb-8">
                <i class="fas fa-basketball-ball text-orange-400 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-400 mb-4">No Basketball Tips Yet</h3>
            <p class="text-slate-500">Basketball predictions will appear here once available. Check back soon!</p>
        </div>
    @endif
</div>
@endsection
