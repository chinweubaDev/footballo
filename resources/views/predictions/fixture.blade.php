@extends('layouts.app')

@section('title', $fixture->home_team . ' vs ' . $fixture->away_team . ' Prediction | Esurebet')
@section('meta_description', $fixture->home_team . ' vs ' . $fixture->away_team . ' match prediction, probabilities and statistical analysis from Esurebet.')
@section('canonical', route('predictions.fixture', ['league' => $league->slug, 'fixture' => $fixture->slug ?? $fixture->id]))

@section('content')
@php
    $snapshot = $fixture->features->first()?->features ?? [];
@endphp

<div class="bg-slate-50 min-h-screen pb-20">
    <section class="relative bg-slate-900 pt-28 pb-20 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #22c55e 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <a href="{{ route('predictions.league', $league->slug) }}" class="inline-block text-slate-400 text-sm mb-6 hover:text-white"><i class="fas fa-arrow-left mr-1"></i> {{ $league->name }}</a>
            <div class="flex items-center justify-center gap-6 mb-4">
                <div class="text-center">
                    @if($fixture->home_team_logo)<img src="{{ $fixture->home_team_logo }}" alt="{{ $fixture->home_team }}" class="w-16 h-16 object-contain mx-auto mb-2">@endif
                    <p class="text-white font-black text-lg">{{ $fixture->home_team }}</p>
                </div>
                <span class="text-slate-500 font-black">VS</span>
                <div class="text-center">
                    @if($fixture->away_team_logo)<img src="{{ $fixture->away_team_logo }}" alt="{{ $fixture->away_team }}" class="w-16 h-16 object-contain mx-auto mb-2">@endif
                    <p class="text-white font-black text-lg">{{ $fixture->away_team }}</p>
                </div>
            </div>
            <p class="text-slate-400">{{ $fixture->match_date?->format('D, M d Y — H:i') }}</p>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10 space-y-6">
        @if(!empty($snapshot['expected_home_goals']) || !empty($snapshot['expected_away_goals']))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Expected Goals</h2>
            <p class="text-slate-700">
                {{ $fixture->home_team }} <strong>{{ $snapshot['expected_home_goals'] ?? '—' }}</strong> —
                <strong>{{ $snapshot['expected_away_goals'] ?? '—' }}</strong> {{ $fixture->away_team }}
            </p>
        </div>
        @endif

        @if($fixture->predictions->isNotEmpty())
            @foreach($fixture->predictions as $prediction)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-black text-primary-600 uppercase tracking-wide">{{ $prediction->category ?? strtoupper($prediction->market_code) }}</span>
                        <p class="text-xl font-black text-slate-900">{{ $prediction->effective_selection }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-slate-900">Probability {{ $prediction->probability }}%</p>
                        <p class="text-sm text-slate-500">Model Confidence {{ $prediction->confidence }}/100</p>
                    </div>
                </div>
                @if($prediction->explanation)
                    <p class="text-sm text-slate-600 mt-4 leading-relaxed">{{ $prediction->explanation }}</p>
                @endif
            </div>
            @endforeach
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center">
                <p class="text-slate-500">No published predictions are available for this match yet.</p>
            </div>
        @endif

        <p class="text-xs text-slate-400 text-center">Predictions are statistical estimates, not guarantees. Football results are unpredictable.</p>
    </div>
</div>
@endsection
