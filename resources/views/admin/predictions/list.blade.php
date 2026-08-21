@extends('layouts.app')

@section('title', 'Predictions - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Predictions</h1>
            <p class="text-gray-600">Review, filter, and manage statistical predictions.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.predictions.list') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Team or fixture ID" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">League</label>
                <select name="league" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($leagues as $league)
                        <option value="{{ $league->api_football_league_id }}" {{ request('league') == $league->api_football_league_id ? 'selected' : '' }}>{{ $league->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Market</label>
                <select name="market" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($markets as $market)
                        <option value="{{ $market->code }}" {{ request('market') == $market->code ? 'selected' : '' }}>{{ $market->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach(['published','no_bet','generated','pending_review','locked'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ strtoupper($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                <select name="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach(['today','tomorrow','3days','7days','custom'] as $d)
                        <option value="{{ $d }}" {{ request('date') == $d ? 'selected' : '' }}>{{ ucfirst($d) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Min Confidence</label>
                <input type="number" name="min_confidence" value="{{ request('min_confidence') }}" placeholder="e.g. 80" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Min Probability</label>
                <input type="number" name="min_probability" value="{{ request('min_probability') }}" placeholder="e.g. 70" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Model Version</label>
                <select name="model_version" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach($modelVersions as $version)
                        <option value="{{ $version }}" {{ request('model_version') == $version ? 'selected' : '' }}>{{ $version }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-4 pb-1">
                <label class="inline-flex items-center text-sm text-gray-700">
                    <input type="checkbox" name="featured" value="1" {{ request('featured') ? 'checked' : '' }} class="rounded border-gray-300 mr-1"> Featured
                </label>
                <label class="inline-flex items-center text-sm text-gray-700">
                    <input type="checkbox" name="overridden" value="1" {{ request('overridden') ? 'checked' : '' }} class="rounded border-gray-300 mr-1"> Overridden
                </label>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700">Filter</button>
                <a href="{{ route('admin.predictions.list') }}" class="px-4 py-2 rounded-lg text-sm text-gray-600 border border-gray-300 hover:bg-gray-50">Reset</a>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fixture</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selection</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prob</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conf</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Q</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Featured</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Override</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($predictions as $prediction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $prediction->fixture?->home_team ?? '—' }} vs {{ $prediction->fixture?->away_team ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $prediction->league?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $prediction->market_code }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $prediction->effective_selection }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $prediction->probability }}%</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $prediction->confidence }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $prediction->data_quality_score }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $prediction->model_version }}</td>
                            <td class="px-4 py-3">
                                @if($prediction->status === 'no_bet')
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800" title="{{ $prediction->no_bet_reason }}">NO BET</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $prediction->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ strtoupper($prediction->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $prediction->featured ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $prediction->is_overridden ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.predictions.show', $prediction) }}" class="text-green-600 text-sm font-semibold hover:underline">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="12" class="px-6 py-10 text-center text-gray-400">No predictions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $predictions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
