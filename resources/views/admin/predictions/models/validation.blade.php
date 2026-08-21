@extends('layouts.app')

@section('title', 'Validation - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $matrix = $report['matrix'] ?? [];
    $markets = ['1x2', 'draw', 'double_chance', 'over_1_5', 'over_2_5', 'btts', 'correct_score'];
    $min = $report['minimum_sample_size'] ?? 100;
    $badge = fn ($status) => match ($status) {
        'STRONG' => 'bg-green-100 text-green-800',
        'PROMISING' => 'bg-emerald-100 text-emerald-800',
        'NEUTRAL' => 'bg-gray-100 text-gray-700',
        'WEAK' => 'bg-red-100 text-red-700',
        default => 'bg-amber-100 text-amber-700',
    };
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Multi-League Validation</h1>
            <p class="text-gray-600">Walk-forward validation across leagues, seasons and markets. Every value shows its sample size.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <!-- League x Market matrix -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">League × Market Accuracy</h2>
                <p class="text-xs text-gray-500">Cell shows accuracy% (n=…). Insufficient samples are marked.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            @foreach($matrix as $league)
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $league['league_name'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($markets as $market)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $market }}</td>
                            @foreach($matrix as $league)
                                @php $m = $league['markets'][$market] ?? null; @endphp
                                <td class="px-4 py-3 text-sm text-right text-gray-700">
                                    @if($m && $m['resolved'] > 0)
                                        {{ $fmt($m['accuracy'], '%') }}
                                        <span class="text-xs {{ $m['resolved'] >= $min ? 'text-gray-400' : 'text-amber-500' }}">(n={{ $m['resolved'] }})</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Market generalization -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Market Generalization</h2>
                <p class="text-xs text-gray-500">Mean/median/std/min/max accuracy across leagues, plus a composite generalization score.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Leagues</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mean</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Median</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Std</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Min</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Max</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gen. Score</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($report['markets'] ?? [] as $market => $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $market }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $s['leagues_evaluated'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['mean_accuracy'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['median_accuracy'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['std_accuracy'], '') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['min_accuracy'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['max_accuracy'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['mean_brier'], '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($s['score'], '') }}</td>
                            <td class="px-4 py-3 text-sm text-left"><span class="px-2 py-1 rounded text-xs font-semibold {{ $badge($s['status'] ?? 'INSUFFICIENT_DATA') }}">{{ $s['status'] ?? 'INSUFFICIENT_DATA' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="px-6 py-8 text-center text-gray-400">No validation data yet. Run predictions:validate or predictions:backfill first.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ranked combinations -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Strongest League × Market Combinations</h2>
                <p class="text-xs text-gray-500">Ranked by accuracy with sample size, Wilson 95% CI and Brier. Ranked by actual data only.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">95% CI</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($report['ranked_combinations'] ?? [] as $c)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $c['league'] }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $c['market'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($c['accuracy'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $fmt($c['ci_lower'], '%').'–'.$fmt($c['ci_upper'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right {{ $c['sufficient'] ? 'text-gray-700' : 'text-amber-500' }}">{{ $c['resolved'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($c['brier'], '', 3) }}</td>
                            <td class="px-4 py-3 text-sm text-left"><span class="px-2 py-1 rounded text-xs font-semibold {{ $badge($c['status'] ?? 'INSUFFICIENT_DATA') }}">{{ $c['status'] ?? 'INSUFFICIENT_DATA' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">No validation data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Data drift -->
        @php $drift = $report['data_drift'] ?? []; @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Data Drift</h2>
                <p class="text-xs text-gray-500">Feature distribution over time ({{ $drift['window_months'] ?? 3 }}-month windows). A red row flags a material shift versus the baseline.</p>
            </div>
            @if(empty($drift['windows'] ?? []))
                <p class="px-6 py-6 text-sm text-gray-400">Insufficient historical data to measure drift.</p>
            @else
                @if(!empty($drift['drift_detected']))
                <div class="px-6 py-3 bg-amber-50 border-b border-amber-100 text-amber-800 text-sm">⚠ DATA DRIFT detected in: {{ implode(', ', $drift['flags']) }}.</div>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Window</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Scoring rate</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Home-win %</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Over 2.5 %</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">BTTS %</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($drift['windows'] as $w)
                            @php $drifted = !empty($w['drifted_metrics']); @endphp
                            <tr class="{{ $drifted ? 'bg-red-50' : 'hover:bg-gray-50' }}">
                                <td class="px-4 py-3 text-sm {{ $drifted ? 'font-semibold text-red-800' : 'text-gray-900' }}">{{ $w['label'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $w['fixtures'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($w['league_scoring_rate'], '', 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($w['home_win_rate'], '%') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($w['over_25_rate'], '%') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($w['btts_rate'], '%') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
