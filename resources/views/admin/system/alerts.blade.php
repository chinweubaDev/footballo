@extends('layouts.app')

@section('title', 'System Alerts - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">System Alerts</h1>
            <p class="text-gray-600">Persistent events for API, generation, settlement and pipeline failures.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severity</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($alerts as $alert)
                        @php
                            $badge = match ($alert->severity) {
                                'CRITICAL' => 'bg-red-600 text-white',
                                'ERROR' => 'bg-red-100 text-red-800',
                                'WARNING' => 'bg-amber-100 text-amber-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ $alert->severity }}</span></td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $alert->type }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $alert->message }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $alert->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $alert->resolved_at ? 'resolved' : 'open' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No alerts.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
