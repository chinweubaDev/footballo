@extends('layouts.app')

@section('title', 'Queue Health - Admin')

@section('content')
@php
    $statusBadge = match ($status ?? '') {
        'HEALTHY' => 'bg-green-100 text-green-800',
        'WARNING' => 'bg-amber-100 text-amber-800',
        'FAILED' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Queue Health</h1>
            <p class="text-gray-600">Queue connection, backlog and failed-job management.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Status</h2>
                <span class="px-4 py-2 rounded-full text-sm font-bold {{ $statusBadge }}">{{ $status ?? 'UNKNOWN' }}</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach([
                    ['Connection', $connection ?? '—'],
                    ['Pending', $pending ?? 0],
                    ['Processing', $processing ?? 0],
                    ['Failed', $failedCount ?? 0],
                    ['Last success', $lastSuccess?->created_at?->diffForHumans() ?? '—'],
                    ['Last worker activity', isset($lastWorkerActivityAt) && $lastWorkerActivityAt ? \Carbon\Carbon::createFromTimestamp($lastWorkerActivityAt)->diffForHumans() : '—'],
                ] as $card)
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xl font-bold text-gray-900">{{ $card[1] }}</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $card[0] }}</p>
                </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-400 mt-4">
                Thresholds: FAILED if a critical event within {{ $thresholds['critical_window_minutes'] }} min;
                WARNING if a failed job within {{ $thresholds['failed_window_hours'] }}h or {{ $thresholds['pending_warning_threshold'] }}+ pending.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Failed Jobs</h2>
                <p class="text-xs text-gray-500">Jobs that exhausted their retries. Retry manually after investigating — no automatic infinite retry.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Queue</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Failed at</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($failedJobs as $job)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $job->display_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $job->queue }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $job->failed_at_dt?->toDateTimeString() ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($job->exception, 200) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.system.queue.retry', $job->id) }}" class="inline">
                                    @csrf
                                    <button class="px-3 py-1 rounded text-xs font-semibold bg-green-600 text-white hover:bg-green-700">Retry</button>
                                </form>
                                <form method="POST" action="{{ route('admin.system.queue.forget', $job->id) }}" class="inline">
                                    @csrf
                                    <button class="px-3 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200">Forget</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No failed jobs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
