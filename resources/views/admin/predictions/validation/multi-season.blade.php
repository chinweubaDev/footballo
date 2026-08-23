@extends('layouts.app')

@section('title', 'Multi-Season Validation - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Multi-Season Validation</h1>
            <p class="text-gray-600">Backtest evidence only — clearly separated from live evidence. Pooled accuracy = total wins ÷ total resolved (never a blind average of percentages).</p>
        </div>

        @include('admin.partials.prediction-nav')

        <!-- Dataset inventory -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold text-gray-900">Dataset Inventory</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Season</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fixtures</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Completed</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Missing</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Completeness</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inventory as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $row['league_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $row['season'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $row['fixtures'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-700">{{ $row['completed'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-amber-700">{{ $row['missing'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($row['completeness'] ?? null, '%', 1) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No historical fixtures imported.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Market generalization -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold text-gray-900">Market Generalization (across seasons)</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pooled n</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pooled acc</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ECE</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Per-season acc (min–max)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">std</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($marketGeneralization as $m)
                        @php $g = $m['generalization']; $series = array_map(fn($s) => $s['accuracy'], $m['per_season']); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $m['model_version'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $m['market_code'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['pooled']['resolved'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['pooled']['accuracy'] ?? null, '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['pooled']['brier_score'] ?? null, '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['pooled']['calibration_error'] ?? null) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ implode(', ', array_map(fn($v) => $fmt($v, '%'), $series)) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($g['std'] ?? null) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No backtest data across seasons yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- League generalization -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold text-gray-900">League Generalization (pooled across seasons)</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Seasons</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ECE</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($leagueGeneralization as $l)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $l['model_version'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $l['league_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $l['n_seasons'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $l['resolved'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-700">{{ $l['won'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-red-700">{{ $l['lost'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['accuracy'] ?? null, '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['brier_score'] ?? null, '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['calibration_error'] ?? null) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="px-6 py-8 text-center text-gray-400">No backtest data across seasons yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
