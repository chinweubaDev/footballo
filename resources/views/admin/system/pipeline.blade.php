@extends('layouts.app')

@section('title', 'Pipeline Health - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Pipeline Health</h1>
            <p class="text-gray-600">Last run status for each pipeline stage.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stage</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last run</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($health as $stage => $h)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($stage)) }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = match ($h['status']) {
                                        'SUCCESS' => 'bg-green-100 text-green-800',
                                        'FAILED' => 'bg-red-100 text-red-800',
                                        'WARNING' => 'bg-amber-100 text-amber-800',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ $h['status'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $h['last_run_at']?->diffForHumans() ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $h['message'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Failed Jobs</h2>
                <p class="text-xs text-gray-500">Jobs that exhausted their retries (failed_jobs table).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Queue</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Failed at</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($failedJobs as $job)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $job->queue }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $job->failed_at }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($job->exception, 200) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">No failed jobs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
