@extends('layouts.app')

@section('title', $category->name . ' Predictions Today | Esurebet')
@section('meta_description', $category->name . ' football predictions and statistical betting tips from Esurebet across all major leagues.')
@section('canonical', url()->current())

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    <section class="relative bg-slate-900 pt-28 pb-20 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #22c55e 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl lg:text-5xl font-black text-white mb-3">{{ $category->name }} Predictions</h1>
            <p class="text-slate-400">Statistical {{ $category->name }} predictions across all major leagues.</p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
        <div class="flex flex-wrap gap-2 mb-8">
            @foreach(\App\Models\PredictionCategory::where('enabled', true)->orderBy('sort_order')->get() as $navMarket)
                <a href="{{ match($navMarket->code) { '1x2' => route('predictions'), 'over_1_5' => route('predictions.over15'), 'over_2_5' => route('predictions.over25'), 'double_chance' => route('predictions.double-chance'), 'btts' => route('predictions.bts'), 'draw' => route('predictions.draw'), default => route('predictions.correct-score') } }}" class="px-4 py-2 rounded-full text-xs font-bold {{ $navMarket->code === $category->code ? 'bg-primary-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-primary-300' }}">{{ $navMarket->name }}</a>
            @endforeach
        </div>

        @if($fixtures->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($fixtures as $fixture)
                    <x-prediction-card :fixture="$fixture" />
                @endforeach
            </div>
            <div class="mt-8">{{ $fixtures->links() }}</div>
        @else
            <div class="bg-white rounded-3xl border border-slate-100 py-16 text-center">
                <i class="fas fa-search text-3xl text-slate-300 mb-3"></i>
                <p class="text-slate-500 font-semibold">No published {{ $category->name }} predictions are currently available.</p>
            </div>
        @endif

        <p class="text-xs text-slate-400 text-center mt-10">Predictions are statistical estimates, not guarantees. Football results are unpredictable.</p>
    </div>
</div>
@endsection
