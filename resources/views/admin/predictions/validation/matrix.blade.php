@extends('layouts.app')

@section('title', 'Validation Matrix - Admin')

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
            <h1 class="text-3xl font-bold text-gray-900">League × Market × Model Matrix</h1>
            <p class="text-gray-600">Phase 1G.2 — complete validation matrix. Every cell carries its sample size. v1.1.0 is shown as NOT AVAILABLE where it has no backtest data.</p>
        </div>

        @include('admin.partials.prediction-nav')

        @if(!empty($report['model_comparison']))
        <div class="bg-amber-50 border border-amber-100 rounded-xl px-6 py-4 mb-6 text-sm text-amber-800">
            @foreach($report['model_comparison'] as $c)
                @if(!($c['available'] ?? false))
                    <strong>{{ $c['version'] }}:</strong> NOT AVAILABLE — no completed backtest runs. {{ $c['note'] ?? '' }}
                @endif
            @endforeach
        </div>
        @endif

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-8 grid grid-cols-2 md:grid-cols-5 gap-4">
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
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Market</label>
                <select name="market" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach($markets as $code => $label)
                        <option value="{{ $code }}" {{ ($filters['market'] ?? null) === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Min accuracy %</label>
                <input type="number" step="0.01" name="threshold" value="{{ $filters['threshold'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="0">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700">Filter</button>
            </div>
        </form>

        <!-- Matrix table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Matrix</h2>
                <p class="text-xs text-gray-500">{{ count($rows) }} cells shown.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Coverage</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Calibration</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gate {{ $report['thresholds']['publication_gate']['min_probability'] ?? 70 }}/{{ $report['thresholds']['publication_gate']['min_confidence'] ?? 75 }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sample</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($rows as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $r['league'] }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $r['market_label'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $r['model'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $r['n'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($r['accuracy'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($r['coverage'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($r['brier'], '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($r['calibration'], '') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($r['gate']['accuracy'] ?? null, '%') }} <span class="text-xs text-gray-400">(n={{ $r['gate']['n'] ?? 0 }})</span></td>
                            <td class="px-4 py-3 text-sm"><span class="px-2 py-1 rounded text-xs font-semibold {{ $badge($r['sample_status']) }}">{{ $r['sample_status'] }}</span></td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <details>
                                    <summary class="cursor-pointer text-green-700 hover:underline">breakdown</summary>
                                    <div class="mt-3 space-y-3 text-xs">
                                        @if(!empty($r['selections']))
                                        <div>
                                            <div class="font-semibold text-gray-700 mb-1">Selections</div>
                                            <table class="w-full border border-gray-100">
                                                @foreach($r['selections'] as $sel => $s)
                                                <tr>
                                                    <td class="px-2 py-1 text-gray-700">{{ $sel }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $s['n'] }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($s['accuracy'], '%') }}</td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                        @endif

                                        <div>
                                            <div class="font-semibold text-gray-700 mb-1">Gate comparison (prob/conf → acc, coverage)</div>
                                            <table class="w-full border border-gray-100">
                                                @foreach($r['gate_comparison'] as $g)
                                                <tr class="{{ ($g['min_probability'] == 70 && $g['min_confidence'] == 75) ? 'bg-green-50' : '' }}">
                                                    <td class="px-2 py-1 text-gray-700">{{ $g['min_probability'] }}/{{ $g['min_confidence'] }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $g['n'] }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($g['accuracy'], '%') }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($g['coverage'], '%') }}</td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        </div>

                                        <div>
                                            <div class="font-semibold text-gray-700 mb-1">Calibration buckets (50-59 … 90-100)</div>
                                            <table class="w-full border border-gray-100">
                                                <tr class="text-gray-500">
                                                    <th class="px-2 py-1 text-left font-medium">Bucket</th>
                                                    <th class="px-2 py-1 text-right font-medium">n</th>
                                                    <th class="px-2 py-1 text-right font-medium">Avg prob</th>
                                                    <th class="px-2 py-1 text-right font-medium">Actual</th>
                                                    <th class="px-2 py-1 text-right font-medium">Gap</th>
                                                </tr>
                                                @foreach($r['calibration_buckets'] as $b)
                                                <tr>
                                                    <td class="px-2 py-1">{{ $b['label'] }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $b['n'] }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($b['avg_probability'], '%') }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($b['actual'], '%') }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($b['gap'], '') }}</td>
                                                </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="px-6 py-8 text-center text-gray-400">No data matches the filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
