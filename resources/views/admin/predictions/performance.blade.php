@extends('layouts.app')

@section('title', 'Model Performance - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $badge = fn ($ok) => $ok
        ? 'bg-green-100 text-green-800'
        : 'bg-amber-100 text-amber-800';
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Esurebet Model Performance</h1>
            <p class="text-gray-600">Historical accuracy measurement — this system measures performance; it does not guarantee it.</p>
        </div>

        @include('admin.partials.prediction-nav')

        @php $o = $overview ?? []; @endphp

        <!-- Overview -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Overview</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                @foreach([
                    ['Total Predictions', $o['total'] ?? 0],
                    ['Resolved', $o['resolved'] ?? 0],
                    ['Wins', $o['won'] ?? 0],
                    ['Losses', $o['lost'] ?? 0],
                    ['Voids', $o['void'] ?? 0],
                    ['Accuracy', $fmt($o['accuracy'] ?? null, '%')],
                    ['Coverage', $fmt($o['coverage_percent'] ?? null, '%')],
                    ['Avg Confidence', $fmt($o['avg_confidence'] ?? null)],
                ] as $card)
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $card[1] }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $card[0] }}</p>
                </div>
                @endforeach
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($o['brier_score'] ?? null, '', 4) }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Brier Score (lower better)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($o['log_loss'] ?? null, '', 4) }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Log Loss (lower better)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($o['avg_probability'] ?? null, '%') }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Avg Probability</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $minimum_sample_size ?? 100 }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Min Sample Size</p>
                </div>
            </div>
        </div>

        <!-- League performance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">League Performance</h2>
                <p class="text-xs text-gray-500">Sample sizes shown — leagues below {{ $minimum_sample_size ?? 100 }} resolved predictions are flagged as insufficient.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Conf.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($leagues ?? [] as $league)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $league['league_name'] }}
                                @if($league['insufficient'])
                                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge(false) }}">Insufficient sample</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $league['resolved'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $league['won'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $league['lost'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($league['accuracy'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($league['avg_confidence']) }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($league['brier_score'], '', 3) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">No resolved predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Market performance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Market Performance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Conf.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($markets ?? [] as $market)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $market['market_code'] }}
                                @if($market['insufficient'])
                                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge(false) }}">Insufficient sample</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $market['resolved'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $market['won'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $market['lost'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($market['accuracy'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($market['avg_confidence']) }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($market['brier_score'], '', 3) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-400">No resolved predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Confidence performance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Confidence Performance</h2>
                <p class="text-xs text-gray-500">Does higher model confidence actually correlate with better results?</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confidence</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($confidence ?? [] as $bucket)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $bucket['label'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $bucket['resolved'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $bucket['won'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $bucket['lost'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($bucket['accuracy'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($bucket['brier_score'], '', 3) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No resolved predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Probability calibration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Probability Calibration</h2>
                <p class="text-xs text-gray-500">Predicted probability vs actual success frequency. Over a large sample they should be close.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Predicted %</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Predicted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actual Success</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gap</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($calibration ?? [] as $bucket)
                        @php $gap = ($bucket['accuracy'] !== null && $bucket['avg_probability'] !== null) ? round($bucket['accuracy'] - $bucket['avg_probability'], 2) : null; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $bucket['label'] }}%</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $bucket['resolved'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($bucket['avg_probability'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($bucket['accuracy'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($gap, ' pts', 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No resolved predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Model versions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Model Versions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Version</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($model_versions ?? [] as $version)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $version['model_version'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $version['resolved'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $version['won'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $version['lost'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($version['accuracy'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($version['brier_score'], '', 3) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No resolved predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Admin overrides -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Admin Overrides vs Original Model</h2>
                @php $ov = $overrides ?? ['model' => [], 'override' => [], 'overridden_count' => 0]; @endphp
                <p class="text-sm text-gray-500 mb-4">Overridden predictions: {{ $ov['overridden_count'] }}. Does manual intervention actually improve results?</p>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"></th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900">Original Model</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-700">{{ $ov['model']['total'] ?? 0 }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-700">{{ $ov['model']['won'] ?? 0 }}</td>
                            <td class="px-4 py-2 text-sm text-right font-semibold text-gray-900">{{ $fmt($ov['model']['accuracy'] ?? null, '%') }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900">Admin Overrides</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-700">{{ $ov['override']['total'] ?? 0 }}</td>
                            <td class="px-4 py-2 text-sm text-right text-gray-700">{{ $ov['override']['won'] ?? 0 }}</td>
                            <td class="px-4 py-2 text-sm text-right font-semibold text-gray-900">{{ $fmt($ov['override']['accuracy'] ?? null, '%') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">NO_BET Analysis</h2>
                @php $nb = $no_bet ?? ['count' => 0, 'total_fixtures' => 0, 'predicted_fixtures' => 0, 'coverage_percent' => null, 'would_be' => []]; @endphp
                <p class="text-sm text-gray-500 mb-4">
                    The model declined {{ $nb['count'] }} selections. Coverage:
                    <strong>{{ $fmt($nb['coverage_percent'] ?? null, '%') }}</strong>
                    ({{ $nb['predicted_fixtures'] ?? 0 }} / {{ $nb['total_fixtures'] ?? 0 }} fixtures).
                </p>
                @php $wb = $nb['would_be'] ?? []; @endphp
                <p class="text-sm text-gray-500">Would-be result of the declined selections (evaluation only — not a recommendation):</p>
                <p class="text-sm mt-2">
                    <span class="font-semibold text-gray-900">{{ $wb['won'] ?? 0 }} won</span> /
                    <span class="font-semibold text-gray-900">{{ $wb['lost'] ?? 0 }} lost</span> —
                    accuracy {{ $fmt($wb['accuracy'] ?? null, '%') }}.
                </p>
            </div>
        </div>

        <!-- Selectivity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Selectivity</h2>
                <p class="text-xs text-gray-500">Does raising the confidence bar improve accuracy? Coverage always shown.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filter</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($selectivity ?? [] as $label => $tier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ $label }} confidence</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $tier['total'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $tier['won'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $tier['lost'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($tier['accuracy'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($tier['brier_score'], '', 3) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No resolved predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Performance over time -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Performance Over Time (Model Drift)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($over_time ?? [] as $period)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $period['month'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $period['total'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $period['won'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $period['lost'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($period['accuracy'], '%') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No resolved predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
