@extends('layouts.app')

@section('title', 'Prediction Center - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Esurebet Prediction Center</h1>
            <p class="text-gray-600">Manage leagues, markets, and statistical predictions.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4 mb-8">
            @foreach([
                ['Upcoming Fixtures', $stats['upcoming_fixtures'], 'fa-futbol', 'text-blue-600 bg-blue-50'],
                ['Generated', $stats['generated'], 'fa-cogs', 'text-gray-600 bg-gray-50'],
                ['Published', $stats['published'], 'fa-check-circle', 'text-green-600 bg-green-50'],
                ['NO BET', $stats['no_bet'], 'fa-ban', 'text-amber-600 bg-amber-50'],
                ['Featured', $stats['featured'], 'fa-star', 'text-purple-600 bg-purple-50'],
                ['Enabled Leagues', $stats['enabled_leagues'], 'fa-trophy', 'text-indigo-600 bg-indigo-50'],
                ['Enabled Markets', $stats['enabled_markets'], 'fa-sliders-h', 'text-rose-600 bg-rose-50'],
            ] as $card)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fas {{ $card[2] }} {{ $card[3] }} px-2 py-1 rounded-md"></i>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $card[1] }}</p>
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $card[0] }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Active model -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Active Prediction Model</h2>
                @if($activeModel)
                    <p class="text-gray-800 font-semibold">{{ $activeModel->name }}</p>
                    <p class="text-sm text-gray-500">Version: {{ $activeModel->version }}</p>
                @else
                    <p class="text-sm text-gray-500">No active model configured.</p>
                @endif
            </div>

            <!-- Recent overrides -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Recent Overrides</h2>
                @forelse($recentOverrides as $override)
                    <div class="py-2 border-b border-gray-50 text-sm">
                        <span class="text-gray-500">{{ $override->original_selection }}</span>
                        <i class="fas fa-arrow-right mx-2 text-gray-300"></i>
                        <span class="font-semibold text-gray-900">{{ $override->new_selection }}</span>
                        <span class="text-gray-400"> — {{ $override->prediction?->fixture?->home_team ?? '—' }} vs {{ $override->prediction?->fixture?->away_team ?? '—' }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No overrides yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Latest predictions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Latest Predictions</h2>
                <a href="{{ route('admin.predictions.list') }}" class="text-sm text-green-600 font-semibold hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Match</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selection</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Probability</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Confidence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($latestPredictions as $prediction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">
                                <a href="{{ route('admin.predictions.show', $prediction) }}" class="hover:underline">
                                    {{ $prediction->fixture?->home_team ?? '—' }} vs {{ $prediction->fixture?->away_team ?? '—' }}
                                </a>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $prediction->market_code }}</td>
                            <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ $prediction->effective_selection }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $prediction->probability }}%</td>
                            <td class="px-6 py-3 text-sm text-gray-700">{{ $prediction->confidence }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $prediction->status === 'published' ? 'bg-green-100 text-green-800' : ($prediction->status === 'no_bet' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700') }}">
                                    {{ strtoupper($prediction->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
