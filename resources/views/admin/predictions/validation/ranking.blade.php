@extends('layouts.app')

@section('title', 'Validation Ranking - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $badge = fn ($s) => match ($s) {
        'ADEQUATE' => 'bg-green-100 text-green-800',
        'LOW' => 'bg-amber-100 text-amber-800',
        default => 'bg-red-100 text-red-700',
    };
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Validation Ranking</h1>
            <p class="text-gray-600">Ranked League × Market combinations. Ranked by a composite score (accuracy, Brier, coverage, calibration, sample size) — never accuracy alone.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-8 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Season</label>
                <input type="number" name="season" value="{{ $filters['season'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="2025">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Model</label>
                <select name="model" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach($models as $m)
                        <option value="{{ $m }}" {{ ($filters['model'] ?? null) === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700">Filter</button>
            </div>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">League × Market Ranking</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Coverage</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Calibration</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sample size</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Score</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($ranking as $c)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $c['rank'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $c['league'] }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $c['market_label'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $c['model'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($c['accuracy'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($c['coverage'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($c['brier'], '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($c['calibration'], '') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $c['n'] }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($c['score'], '') }}</td>
                            <td class="px-4 py-3 text-sm"><span class="px-2 py-1 rounded text-xs font-semibold {{ $badge($c['sample_status']) }}">{{ $c['sample_status'] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="px-6 py-8 text-center text-gray-400">No validation data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
