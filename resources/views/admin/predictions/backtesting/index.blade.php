@extends('layouts.app')

@section('title', 'Backtesting - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Backtesting</h1>
                <p class="text-gray-600">Historical prediction evaluation on stored match data.</p>
            </div>
            <a href="{{ route('admin.predictions.backtesting.create') }}"
               class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
                <i class="fas fa-play mr-1"></i> New Backtest
            </a>
        </div>

        @include('admin.partials.prediction-nav')

        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Season</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Markets</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Range</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Preds</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ROI</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($runs as $run)
                        @php $m = $run->metrics['overview'] ?? []; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $run->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $run->league?->name ?? 'All leagues' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $run->season ?? 'All' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ is_array($run->markets) && count($run->markets) ? implode(', ', $run->markets) : 'All' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $run->date_start?->format('Y-m-d') ?? '—' }} → {{ $run->date_end?->format('Y-m-d') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $run->model_version }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['total'] ?? $run->generated_predictions }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['won'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['lost'] ?? 0 }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                                {{ isset($m['accuracy']) ? number_format((float) $m['accuracy'], 2).'%' : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500" title="Historical odds are not stored, so ROI cannot be computed without fabricating odds.">
                                N/A
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colors = [
                                        'queued' => 'bg-gray-100 text-gray-700',
                                        'running' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-amber-100 text-amber-800',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $colors[$run->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ strtoupper($run->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $run->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.predictions.backtesting.show', $run) }}" class="text-green-600 font-semibold hover:underline">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="14" class="px-6 py-8 text-center text-gray-400">
                            No backtests yet. <a href="{{ route('admin.predictions.backtesting.create') }}" class="text-green-600 hover:underline">Create one</a>.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $runs->links() }}
        </div>
    </div>
</div>
@endsection
