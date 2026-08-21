@extends('layouts.app')

@section('title', 'Compare Models - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $markets = ['1x2', 'draw', 'double_chance', 'over_1_5', 'over_2_5', 'btts', 'correct_score'];
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Compare Models</h1>
            <p class="text-gray-600">Side-by-side comparison of two model versions on resolved data.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-8 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Model A</label>
                <select name="a" class="rounded-lg border-gray-300 shadow-sm focus:border-green-500">
                    @foreach($versions as $v)
                        <option value="{{ $v }}" {{ $a === $v ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Model B</label>
                <select name="b" class="rounded-lg border-gray-300 shadow-sm focus:border-green-500">
                    @foreach($versions as $v)
                        <option value="{{ $v }}" {{ $b === $v ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700">Compare</button>
        </form>

        @if($dataA && $dataB)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach([['A', $a, $dataA], ['B', $b, $dataB]] as [$label, $version, $data])
                @php $o = $data['overview']; @endphp
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="font-semibold text-gray-900 mb-2">Model {{ $label }} — {{ $version }}</p>
                    <div class="grid grid-cols-2 gap-2 text-sm text-gray-700">
                        <span>Resolved:</span><span class="text-right font-semibold">{{ $o['resolved'] }}</span>
                        <span>Wins:</span><span class="text-right">{{ $o['won'] }}</span>
                        <span>Losses:</span><span class="text-right">{{ $o['lost'] }}</span>
                        <span>Accuracy:</span><span class="text-right font-semibold">{{ $fmt($o['accuracy'], '%') }}</span>
                        <span>Brier:</span><span class="text-right">{{ $fmt($o['brier_score'], '', 4) }}</span>
                        <span>Log loss:</span><span class="text-right">{{ $fmt($o['log_loss'], '', 4) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @if($significance)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Statistical Significance</h2>
            <p class="text-xs text-gray-500 mb-4">Two-proportion z-test (α = {{ $significance['alpha'] }}). A difference is only reported when statistically significant AND both samples are ≥ {{ $significance['minimum_sample'] }}.</p>
            @php $o = $significance['overall']; @endphp
            <div class="rounded-lg p-4 mb-4 {{ $o['verdict'] === 'improvement' ? 'bg-green-50 border border-green-200' : ($o['verdict'] === 'regression' ? 'bg-red-50 border border-red-200' : 'bg-gray-50 border border-gray-200') }}">
                <p class="font-semibold text-gray-900">
                    Overall: {{ $significance['version_a'] }} {{ $fmt($o['a_accuracy'], '%') }} (n={{ $o['a_n'] }}) vs {{ $significance['version_b'] }} {{ $fmt($o['b_accuracy'], '%') }} (n={{ $o['b_n'] }})
                    — Δ {{ $fmt($o['diff_points'], ' pts') }} (95% CI: {{ $fmt($o['ci_lower'], '') }} to {{ $fmt($o['ci_upper'], '') }}, p={{ $fmt($o['p_value'], '', 4) }})
                </p>
                <p class="text-sm mt-1 {{ $o['verdict'] === 'improvement' ? 'text-green-700' : ($o['verdict'] === 'regression' ? 'text-red-700' : 'text-gray-600') }}">{{ $o['message'] }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $significance['version_a'] }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $significance['version_b'] }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Δ</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">p</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verdict</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($significance['markets'] as $market => $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $market }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['a_accuracy'], '%').' (n='.$s['a_n'].')' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['b_accuracy'], '%').' (n='.$s['b_n'].')' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['diff_points'], '') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($s['p_value'], '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-left {{ $s['verdict'] === 'improvement' ? 'text-green-700 font-semibold' : ($s['verdict'] === 'regression' ? 'text-red-700 font-semibold' : 'text-gray-500') }}">{{ $s['message'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">By Market</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $a }} acc</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $a }} brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $b }} acc</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $b }} brier</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($markets as $market)
                        @php $ma = $dataA['by_market'][$market] ?? null; $mb = $dataB['by_market'][$market] ?? null; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $market }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $ma ? $fmt($ma['accuracy'], '%').' (n='.$ma['resolved'].')' : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $ma ? $fmt($ma['brier_score'], '', 4) : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $mb ? $fmt($mb['accuracy'], '%').' (n='.$mb['resolved'].')' : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $mb ? $fmt($mb['brier_score'], '', 4) : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Probability Calibration (predicted vs actual)</h2>
                <p class="text-xs text-gray-500">A well-calibrated model approaches the 45° line. Values above the line are overconfident.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Predicted %</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $a }} actual</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $b }} actual</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($dataA['calibration'] as $bucket)
                        @php $label = $bucket['label']; $cb = collect($dataB['calibration'])->firstWhere('label', $label); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $label }}%</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($bucket['accuracy'], '%') }} <span class="text-xs text-gray-400">(pred {{ $fmt($bucket['avg_probability'], '%') }})</span></td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $cb ? $fmt($cb['accuracy'], '%').' <span class="text-xs text-gray-400">(pred '.$fmt($cb['avg_probability'], '%').')</span>' : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-400">
            Select two model versions to compare.
        </div>
        @endif
    </div>
</div>
@endsection
