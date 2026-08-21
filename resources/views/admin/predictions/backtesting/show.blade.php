@extends('layouts.app')

@section('title', 'Backtest #' . $run->id . ' - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $m = $metrics['overview'] ?? [];
    $cov = $metrics['coverage_percent'] ?? null;
    $nb = $metrics['no_bet'] ?? [];
    $colors = [
        'queued' => 'bg-gray-100 text-gray-700',
        'running' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'failed' => 'bg-red-100 text-red-800',
        'cancelled' => 'bg-amber-100 text-amber-800',
    ];
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Backtest #{{ $run->id }}</h1>
                <p class="text-gray-600">{{ $run->league?->name ?? 'All leagues' }} · {{ $run->season ?? 'All seasons' }} · {{ $run->model_version }}</p>
            </div>
            <div class="flex gap-2">
                @if($run->status === 'completed')
                <a href="{{ route('admin.predictions.backtesting.export', $run) }}"
                   class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                    <i class="fas fa-download mr-1"></i> Export CSV
                </a>
                @endif
                @if(!$run->is_finished && $run->status !== 'queued')
                <form action="{{ route('admin.predictions.backtesting.cancel', $run) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm font-semibold hover:bg-red-100">
                        <i class="fas fa-stop mr-1"></i> Cancel
                    </button>
                </form>
                @endif
                <a href="{{ route('admin.predictions.backtesting.index') }}" class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50">Back</a>
            </div>
        </div>

        @include('admin.partials.prediction-nav')

        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Status + progress -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $colors[$run->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ strtoupper($run->status) }}
                    </span>
                    <span class="ml-4 text-sm text-gray-500">Created {{ $run->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="text-sm text-gray-600">
                    Processed: <strong>{{ $run->processed_fixtures }}</strong> / {{ $run->total_fixtures }}
                </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-green-600 h-3 rounded-full" style="width: {{ $run->progress_percent }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $run->progress_percent }}% complete</p>

            @if($run->error)
                <div class="mt-4 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ $run->error }}
                </div>
            @endif
        </div>

        @if($run->status === 'completed')
        <!-- Overview -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Results</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                @foreach([
                    ['Total Predictions', $m['total'] ?? 0],
                    ['Wins', $m['won'] ?? 0],
                    ['Losses', $m['lost'] ?? 0],
                    ['Voids', $m['void'] ?? 0],
                    ['Accuracy', $fmt($m['accuracy'] ?? null, '%')],
                    ['Avg Probability', $fmt($m['avg_probability'] ?? null, '%')],
                    ['Avg Confidence', $fmt($m['avg_confidence'] ?? null)],
                    ['Coverage', $fmt($cov, '%')],
                ] as $card)
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $card[1] }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $card[0] }}</p>
                </div>
                @endforeach
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($m['brier_score'] ?? null, '', 4) }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Brier Score</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $fmt($m['log_loss'] ?? null, '', 4) }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Log Loss</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $nb['count'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">NO_BET selections</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">N/A</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">ROI (no historical odds)</p>
                </div>
            </div>
        </div>

        <!-- By market -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">By Market</h2>
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
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($metrics['by_market'] ?? [] as $code => $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $code }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $row['resolved'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $row['won'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $row['lost'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($row['accuracy'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($row['brier_score'], '', 3) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No market data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Confidence buckets -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Confidence Performance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confidence</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($metrics['confidence_buckets'] ?? [] as $bucket)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $bucket['label'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $bucket['resolved'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $bucket['won'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($bucket['accuracy'], '%') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No confidence data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Probability calibration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Probability Calibration</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Predicted %</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Predicted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actual Success</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($metrics['probability_buckets'] ?? [] as $bucket)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $bucket['label'] }}%</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $bucket['resolved'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($bucket['avg_probability'], '%') }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $fmt($bucket['accuracy'], '%') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No calibration data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Selectivity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Selectivity</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filter</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predictions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($metrics['selectivity'] ?? [] as $label => $tier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ $label }} confidence</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $tier['total'] }}</td>
                            <td class="px-6 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($tier['accuracy'], '%') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">No selectivity data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
