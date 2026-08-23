@extends('layouts.app')

@section('title', 'Live Validation & Model Comparison - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $evidenceBadge = fn ($label) => match($label) {
        'STRONGER EVIDENCE' => 'bg-green-100 text-green-800',
        'MEANINGFUL' => 'bg-blue-100 text-blue-800',
        'PRELIMINARY' => 'bg-amber-100 text-amber-800',
        default => 'bg-gray-100 text-gray-600',
    };
    $summary = $summary ?? ['total_resolved' => 0, 'evidence' => 'INSUFFICIENT', 'models' => []];
    $paired = $paired ?? [];
    $agreement = $agreement ?? [];
    $gates = $gates ?? [];
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Live Validation &amp; Model Comparison</h1>
            <p class="text-gray-600">Prospective evidence after real matches have finished. This system measures; it never trains, tunes, or fabricates.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <!-- Live evidence banner -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Live Evidence</h2>
                    <p class="text-sm text-gray-600">{{ $summary['total_resolved'] }} resolved live predictions.</p>
                </div>
                <span class="px-4 py-2 rounded-full text-sm font-bold {{ $evidenceBadge($summary['evidence']) }}">
                    {{ $summary['total_resolved'] === 0 ? 'INSUFFICIENT LIVE EVIDENCE' : $summary['evidence'] }}
                </span>
            </div>
        </div>

        <!-- Pipeline state counters -->
        @php $c = $summary['counters'] ?? []; @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
            @foreach([
                ['Total predicted', $c['total_predictions'] ?? 0],
                ['Locked', $c['total_locked'] ?? 0],
                ['In progress', $c['total_locked'] - ($c['total_settled'] ?? 0)],
                ['Settled', $c['total_settled'] ?? 0],
                ['Pending review', $c['total_pending_review'] ?? 0],
                ['Provenance invalid', $c['total_provenance_invalid'] ?? 0],
                ['Provenance uncertain', $c['total_provenance_uncertain'] ?? 0],
            ] as $card)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xl font-bold text-gray-900">{{ $card[1] }}</p>
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $card[0] }}</p>
            </div>
            @endforeach
        </div>

        <!-- Per-model summary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Model Summary</h2>
                <p class="text-xs text-gray-500">Resolved / wins / losses / voids / accuracy / Brier / log loss / ECE / evidence.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Published</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">No-bet</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Coverage</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Resolved</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Voids</th>
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
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                {{ $m['model_version'] }} @if($m['active'])<span class="ml-1 text-xs text-green-700">ACTIVE</span>@else<span class="ml-1 text-xs text-indigo-600">SHADOW</span>@endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-green-700">{{ $cm['published'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $cm['no_bet'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($cm['coverage'] ?? null, '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['resolved'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-700">{{ $m['won'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-red-700">{{ $m['lost'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $m['void'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $m['resolved'] > 0 ? $fmt($m['accuracy'] ?? null, '%') : '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['brier_score'] ?? null, '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['log_loss'] ?? null, '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['calibration_error'] ?? null) }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $evidenceBadge($m['evidence']) }}">{{ $m['evidence'] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="13" class="px-6 py-8 text-center text-gray-400">No resolved live predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paired model comparison -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Paired Model Comparison</h2>
                <p class="text-xs text-gray-500">{{ $paired['version_a'] ?? 'v1.0.0' }} vs {{ $paired['version_b'] ?? 'v1.1.0' }} on identical resolved fixtures.</p>
            </div>
            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                @php $wm = $paired['win_matrix'] ?? []; @endphp
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $wm['both_won'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 uppercase">Both won</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $wm['a_won_b_lost'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 uppercase">{{ $paired['version_a'] ?? 'A' }} won / {{ $paired['version_b'] ?? 'B' }} lost</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $wm['a_lost_b_won'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 uppercase">{{ $paired['version_a'] ?? 'A' }} lost / {{ $paired['version_b'] ?? 'B' }} won</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $wm['both_lost'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 uppercase">Both lost</p>
                </div>
            </div>
            @php $diffs = $paired['diffs'] ?? []; $mc = $paired['mcnemar'] ?? []; @endphp
            <div class="px-6 pb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($diffs['accuracy'] ?? null, ' pts') }}</p>
                    <p class="text-xs text-gray-500 uppercase">Accuracy diff</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($diffs['brier_score'] ?? null, '', 4) }}</p>
                    <p class="text-xs text-gray-500 uppercase">Brier diff (neg = B better)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($diffs['log_loss'] ?? null, '', 4) }}</p>
                    <p class="text-xs text-gray-500 uppercase">Log loss diff (neg = B better)</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($diffs['calibration_error'] ?? null, '', 4) }}</p>
                    <p class="text-xs text-gray-500 uppercase">ECE diff (neg = B better)</p>
                </div>
            </div>
            <div class="px-6 pb-6 text-sm text-gray-600">
                McNemar (paired classification): discordant = {{ $mc['discordant'] ?? 0 }}, p = {{ $mc['p_value'] ?? '—' }} {{ ($mc['significant'] ?? false) ? '(significant)' : '' }}
            </div>
        </div>

        <!-- Model agreement -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Model Agreement (selection)</h2>
                <p class="text-xs text-gray-500">Same selection vs different selection per market.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pairs</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Same</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Different</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Agreement</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($agreement['markets'] ?? [] as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $m['market_code'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['pairs'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['same_selection'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['different_selection'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['agreement_percent'] ?? null, '%') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No paired resolved predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gate analysis -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Gate Analysis (analytical only)</h2>
                <p class="text-xs text-gray-500">60/60 through 80/80. Analysis only — production gates are NOT changed.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gate</th>
                            @foreach($gates[0]['models'] ?? [] as $version => $g)
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $version }} n</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $version }} acc</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $version }} cov</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ $version }} brier</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($gates as $gate)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $gate['label'] }}</td>
                            @foreach($gate['models'] ?? [] as $g)
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $g['resolved'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($g['accuracy'] ?? null, '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($g['coverage'] ?? null, '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($g['brier_score'] ?? null, '', 4) }}</td>
                            @endforeach
                        </tr>
                        @empty
                        <tr><td colspan="13" class="px-6 py-8 text-center text-gray-400">No resolved predictions for gate analysis.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sure picks & most featured -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @foreach([['Sure Picks (1X2)', $surePicks ?? []], ['Most Featured (1X2 + DC)', $mostFeatured ?? []]] as $block)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $block[0] }}</h2>
                @php $s = $block[1]; @endphp
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Generated</dt><dd class="font-bold text-gray-900">{{ $s['generated'] ?? 0 }}</dd></div>
                    <div><dt class="text-gray-500">Published</dt><dd class="font-bold text-gray-900">{{ $s['published'] ?? 0 }}</dd></div>
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

        <div class="text-sm text-gray-500">
            Export the live-validation audit dataset:
            <a href="{{ route('admin.predictions.performance.export') }}" class="text-green-700 font-semibold hover:underline">Download CSV</a>.
        </div>
    </div>
</div>
@endsection
