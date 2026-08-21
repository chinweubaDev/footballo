@extends('layouts.app')

@section('title', $league->name . ' Predictions Today | Esurebet')
@section('meta_description', $league->name . ' predictions, match tips, 1X2, Over 1.5, Over 2.5, BTTS and Double Chance predictions from Esurebet.')
@section('canonical', route('predictions.league', $league->slug))
@section('og_title', $league->name . ' Predictions | Esurebet')
@section('og_description', $league->name . ' match predictions and statistical betting tips from Esurebet.')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    <section class="relative bg-slate-900 pt-28 pb-20 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #22c55e 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
                @if($league->logo)
                    <img src="{{ $league->logo }}" alt="{{ $league->name }}" class="w-14 h-14 object-contain">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center"><i class="fas fa-trophy text-white/70 text-2xl"></i></div>
                @endif
            </div>
            <h1 class="text-3xl lg:text-5xl font-black text-white mb-3">{{ $league->name }} Predictions</h1>
            <p class="text-slate-400">{{ $league->country }} · Season {{ $league->season }}</p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
        <!-- Filters -->
        <form method="GET" action="{{ route('predictions.league', $league->slug) }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex flex-wrap gap-3 mb-8">
            <select name="market" onchange="this.form.submit()" class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700">
                <option value="">All Markets</option>
                @foreach($markets as $market)
                    <option value="{{ $market->code }}" {{ $marketCode === $market->code ? 'selected' : '' }}>{{ $market->name }}</option>
                @endforeach
            </select>
            <select name="date" onchange="this.form.submit()" class="border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700">
                @foreach(['all' => 'All Upcoming', 'today' => 'Today', 'tomorrow' => 'Tomorrow', '3days' => 'Next 3 Days', '7days' => 'Next 7 Days'] as $value => $label)
                    <option value="{{ $value }}" {{ $dateRange === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @if($marketCode || $dateRange !== 'all')
                <a href="{{ route('predictions.league', $league->slug) }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-500 border border-slate-200 rounded-xl hover:bg-slate-50"><i class="fas fa-times mr-1"></i> Reset</a>
            @endif
        </form>

        <!-- League navigation -->
        <div class="flex flex-wrap gap-2 mb-8">
            @foreach(\App\Models\League::where('enabled', true)->where('homepage_enabled', true)->orderBy('priority')->get() as $navLeague)
                <a href="{{ route('predictions.league', $navLeague->slug) }}" class="px-4 py-2 rounded-full text-xs font-bold {{ $navLeague->slug === $league->slug ? 'bg-primary-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-primary-300' }}">{{ $navLeague->name }}</a>
            @endforeach
        </div>

        <!-- Predictions -->
        @if($fixtures->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($fixtures as $fixture)
                    <x-prediction-card :fixture="$fixture" />
                @endforeach
            </div>
            <div class="mt-8">
                {{ $fixtures->links() }}
            </div>
        @else
            <div class="bg-white rounded-3xl border border-slate-100 py-16 text-center">
                <i class="fas fa-search text-3xl text-slate-300 mb-3"></i>
                <p class="text-slate-500 font-semibold">No predictions are currently available for this league.</p>
            </div>
        @endif

        <p class="text-xs text-slate-400 text-center mt-10">Predictions are statistical estimates, not guarantees. Football results are unpredictable.</p>
    </div>
</div>
@endsection
