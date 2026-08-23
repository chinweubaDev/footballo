@extends('layouts.app')

@section('title', 'API Monitoring - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">API Monitoring</h1>
            <p class="text-gray-600">API-Football request health, rate limits and failures.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs text-gray-500 uppercase">Requests today</div>
                <div class="text-2xl font-bold text-gray-900">{{ $summary['requests_today'] }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs text-gray-500 uppercase">Successful</div>
                <div class="text-2xl font-bold text-green-700">{{ $summary['successful_today'] }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs text-gray-500 uppercase">Failed</div>
                <div class="text-2xl font-bold text-red-700">{{ $summary['failed_today'] }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs text-gray-500 uppercase">429 (rate limited)</div>
                <div class="text-2xl font-bold text-amber-700">{{ $summary['rate_limited_today'] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs text-gray-500 uppercase mb-1">Last success</div>
                <div class="text-sm text-gray-900">{{ $summary['last_success']?->endpoint ?? '—' }}</div>
                <div class="text-xs text-gray-400">{{ $summary['last_success']?->created_at?->diffForHumans() ?? '' }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs text-gray-500 uppercase mb-1">Last failure</div>
                <div class="text-sm text-gray-900">{{ $summary['last_failure']?->endpoint ?? '—' }}</div>
                <div class="text-xs text-gray-400">{{ $summary['last_failure']?->error ?? '' }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs text-gray-500 uppercase mb-1">Remaining quota</div>
                <div class="text-2xl font-bold text-gray-900">{{ $summary['last_quota']?->remaining_quota ?? '—' }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent requests</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Endpoint</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Retries</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Duration</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recent as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $row->endpoint }}</td>
                            <td class="px-4 py-3 text-sm text-right {{ $row->successful ? 'text-green-700' : 'text-red-700' }}">{{ $row->status ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $row->retries }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $row->duration_ms }}ms</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No API requests recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
