@extends('layouts.app')

@section('title', 'Live Model Validation Report - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $evidenceBadge = fn ($label) => match($label) {
        'STRONGER EVIDENCE' => 'bg-green-100 text-green-800',
        'MEANINGFUL' => 'bg-blue-100 text-blue-800',
        'PRELIMINARY' => 'bg-amber-100 text-amber-800',
        default => 'bg-gray-100 text-gray-600',
    };
    $summary = $summary ?? ['total_resolved' => 0, 'evidence' => 'INSUFFICIENT', 'models' => [], 'counters' => []];
    $c = $summary['counters'] ?? [];
    $paired = $paired ?? [];
    $agreement = $agreement ?? [];
    $gates = $gates ?? [];
    $marketByModel = $marketByModel ?? [];
    $leagueByModel = $leagueByModel ?? [];
    $matrixByModel = $matrixByModel ?? [];
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Live Model Validation Report</h1>
                <p class="text-gray-600">Date: {{ $date }} — prospective live evidence. Measurement only; never a guarantee.</p>
            </div>
            <a href="{{ route('admin.predictions.performance.export') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-green-600 text-white hover:bg-green-700">
                <i class="fas fa-download mr-1"></i> Export CSV
            </a>
        </div>

        @include('admin.partials.prediction-nav')

        <!-- Evidence + health strip -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Evidence Status</h2>
                <span class="px-4 py-2 rounded-full text-sm font-bold {{ $evidenceBadge($summary['evidence']) }}">
                    {{ $summary['total_resolved'] === 0 ? 'INSUFFICIENT LIVE EVIDENCE' : $summary['evidence'] }}
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                @foreach([
                    ['Total predicted', $c['total_predictions'] ?? 0],
                    ['Locked', $c['total_locked'] ?? 0],
                    ['Settled', $c['total_settled'] ?? 0],
                    ['Pending review', $c['total_pending_review'] ?? 0],
                    ['Provenance invalid', $c['total_provenance_invalid'] ?? 0],
                    ['Provenance uncertain', $c['total_provenance_uncertain'] ?? 0],
                    ['API requests (today)', $apiHealth['requests_today'] ?? 0],
                    ['Queue status', $queueHealth['status'] ?? 'UNKNOWN'],
                ] as $card)
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $card[1] }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $card[0] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Model summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Model Performance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Published</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">No-bet</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Resolved</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Log Loss</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ECE</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evidence</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($summary['models'] as $m)
                        @php $cm = $c['models'][$m['model_version']] ?? []; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $m['model_version'] }} @if($m['active']) (ACTIVE) @else (SHADOW) @endif</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $cm['predictions'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-700">{{ $cm['published'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $cm['no_bet'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['resolved'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-700">{{ $m['won'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-red-700">{{ $m['lost'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $m['resolved'] > 0 ? $fmt($m['accuracy'] ?? null, '%') : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['brier_score'] ?? null, '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['log_loss'] ?? null, '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['calibration_error'] ?? null) }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $evidenceBadge($m['evidence']) }}">{{ $m['evidence'] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="12" class="px-6 py-8 text-center text-gray-400">No live evidence yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paired comparison -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Model Comparison ({{ $paired['version_a'] ?? 'v1.0.0' }} vs {{ $paired['version_b'] ?? 'v1.1.0' }})</h2>
            @php $wm = $paired['win_matrix'] ?? []; $diffs = $paired['diffs'] ?? []; $mc = $paired['mcnemar'] ?? []; @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $paired['paired'] ?? 0 }}</p><p class="text-xs text-gray-500 uppercase">Paired</p></div>
                <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $wm['both_won'] ?? 0 }}</p><p class="text-xs text-gray-500 uppercase">Both won</p></div>
                <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $wm['a_won_b_lost'] ?? 0 }}</p><p class="text-xs text-gray-500 uppercase">A won / B lost</p></div>
                <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $wm['a_lost_b_won'] ?? 0 }}</p><p class="text-xs text-gray-500 uppercase">A lost / B won</p></div>
                <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $wm['both_lost'] ?? 0 }}</p><p class="text-xs text-gray-500 uppercase">Both lost</p></div>
                <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $fmt($diffs['accuracy'] ?? null, ' pts') }}</p><p class="text-xs text-gray-500 uppercase">Accuracy Δ</p></div>
                <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $fmt($diffs['brier_score'] ?? null, '', 4) }}</p><p class="text-xs text-gray-500 uppercase">Brier Δ</p></div>
                <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $fmt($diffs['calibration_error'] ?? null, '', 4) }}</p><p class="text-xs text-gray-500 uppercase">ECE Δ</p></div>
            </div>
            <p class="text-sm text-gray-600 mt-4">McNemar: discordant {{ $mc['discordant'] ?? 0 }}, p = {{ $mc['p_value'] ?? '—' }}.</p>
        </div>

        <!-- Market performance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold text-gray-900">Market Performance</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr><th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th><th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ECE</th><th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evidence</th></tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $any = false; @endphp
                        @foreach($versions as $version)
                            @foreach($marketByModel[$version] ?? [] as $m)
                            @php $any = true; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $version }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $m['market_code'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['resolved'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-700">{{ $m['won'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['accuracy'] ?? null, '%') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['brier_score'] ?? null, '', 4) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['calibration_error'] ?? null) }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $evidenceBadge($m['evidence']) }}">{{ $m['evidence'] }}</span></td>
                            </tr>
                            @endforeach
                        @endforeach
                        @if(!$any)<tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No market evidence yet.</td></tr>@endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- League performance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-lg font-semibold text-gray-900">League Performance</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr><th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th><th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th><th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ECE</th><th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evidence</th></tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $any = false; @endphp
                        @foreach($versions as $version)
                            @foreach($leagueByModel[$version] ?? [] as $l)
                            @php $any = true; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $version }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $l['league_name'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $l['resolved'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-700">{{ $l['won'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['accuracy'] ?? null, '%') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['brier_score'] ?? null, '', 4) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['calibration_error'] ?? null) }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $evidenceBadge($l['evidence']) }}">{{ $l['evidence'] }}</span></td>
                            </tr>
                            @endforeach
                        @endforeach
                        @if(!$any)<tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No league evidence yet.</td></tr>@endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sure picks & featured -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @foreach([['Sure Picks (1X2)', $surePicks ?? []], ['Most Featured (1X2 + DC)', $mostFeatured ?? []]] as $block)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $block[0] }}</h2>
                @php $s = $block[1]; @endphp
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Resolved</dt><dd class="font-bold text-gray-900">{{ $s['resolved'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Wins</dt><dd class="font-bold text-green-700">{{ $s['wins'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Losses</dt><dd class="font-bold text-red-700">{{ $s['losses'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Accuracy</dt><dd class="font-bold text-gray-900">{{ $fmt($s['accuracy'] ?? null, '%') }}</dd></div>
                    <div><dt class="text-gray-500">Brier</dt><dd class="font-bold text-gray-900">{{ $fmt($s['brier_score'] ?? null, '', 4) }}</dd></div>
                    <div><dt class="text-gray-500">Evidence</dt><dd class="font-bold text-gray-900">{{ $s['evidence'] ?? 'INSUFFICIENT' }}</dd></div>
                </dl>
            </div>
            @endforeach
        </div>

        <!-- Health -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Operational Health</h2>
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><dt class="text-gray-500">API requests (today)</dt><dd class="font-bold text-gray-900">{{ $apiHealth['requests_today'] }}</dd></div>
                <div><dt class="text-gray-500">API failed</dt><dd class="font-bold text-red-700">{{ $apiHealth['failed_today'] }}</dd></div>
                <div><dt class="text-gray-500">API 429</dt><dd class="font-bold text-amber-700">{{ $apiHealth['rate_limited_today'] }}</dd></div>
                <div><dt class="text-gray-500">Avg latency</dt><dd class="font-bold text-gray-900">{{ $apiHealth['avg_duration_ms'] }} ms</dd></div>
                <div><dt class="text-gray-500">Queue pending</dt><dd class="font-bold text-gray-900">{{ $queueHealth['pending'] }}</dd></div>
                <div><dt class="text-gray-500">Queue failed</dt><dd class="font-bold text-red-700">{{ $queueHealth['failed'] }}</dd></div>
                <div><dt class="text-gray-500">Settled</dt><dd class="font-bold text-gray-900">{{ $settlementHealth['settled'] }}</dd></div>
                <div><dt class="text-gray-500">Pending review</dt><dd class="font-bold text-gray-900">{{ $settlementHealth['pending_review'] }}</dd></div>
            </dl>
        </div>
    </div>
</div>
@endsection
