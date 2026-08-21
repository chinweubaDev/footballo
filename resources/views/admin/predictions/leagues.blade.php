@extends('layouts.app')

@section('title', 'Prediction Leagues - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Prediction Leagues</h1>
            <p class="text-gray-600">Control which leagues receive predictions. Lower priority = higher priority.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="space-y-6">
            @forelse($leagues as $league)
            <div class="bg-white rounded-lg shadow-sm border {{ $league->enabled ? 'border-gray-200' : 'border-red-200 bg-red-50/30' }} p-6">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        @if($league->logo)
                            <img src="{{ $league->logo }}" alt="{{ $league->name }}" class="w-10 h-10 object-contain">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-trophy text-gray-400"></i>
                            </div>
                        @endif
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ $league->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $league->country }} · API ID: {{ $league->api_football_league_id }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $league->enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $league->enabled ? 'ENABLED' : 'DISABLED' }}
                        </span>
                        <form method="POST" action="{{ route('admin.predictions.leagues.toggle', $league) }}" onsubmit="return confirm('Toggle this league? Existing predictions are preserved.')">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded-lg text-xs font-semibold {{ $league->enabled ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' }}">
                                {{ $league->enabled ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Prediction Engine</span>
                        <span class="font-semibold {{ $league->prediction_enabled ? 'text-green-700' : 'text-gray-400' }}">{{ $league->prediction_enabled ? 'ON' : 'OFF' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Homepage</span>
                        <span class="font-semibold {{ $league->homepage_enabled ? 'text-green-700' : 'text-gray-400' }}">{{ $league->homepage_enabled ? 'VISIBLE' : 'HIDDEN' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Min Confidence</span>
                        <span class="font-semibold">{{ $league->prediction_min_confidence }}%</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Auto Publish</span>
                        <span class="font-semibold">{{ $league->auto_publish ? 'YES' : 'NO' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Upcoming Fixtures</span>
                        <span class="font-semibold">{{ $league->fixtures_count }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs uppercase">Predictions</span>
                        <span class="font-semibold">{{ $league->predictions_count }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.predictions.leagues.settings', $league) }}" class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end border-t border-gray-100 pt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Season</label>
                        <input type="number" name="season" value="{{ $league->season }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Min Confidence</label>
                        <input type="number" name="prediction_min_confidence" value="{{ $league->prediction_min_confidence }}" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Priority</label>
                        <input type="number" name="priority" value="{{ $league->priority }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex items-center gap-4 col-span-2 md:col-span-2">
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input type="checkbox" name="prediction_enabled" value="1" {{ $league->prediction_enabled ? 'checked' : '' }} class="rounded border-gray-300 mr-1"> Prediction
                        </label>
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input type="checkbox" name="homepage_enabled" value="1" {{ $league->homepage_enabled ? 'checked' : '' }} class="rounded border-gray-300 mr-1"> Homepage
                        </label>
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input type="checkbox" name="auto_publish" value="1" {{ $league->auto_publish ? 'checked' : '' }} class="rounded border-gray-300 mr-1"> Auto Publish
                        </label>
                    </div>
                    <div class="md:col-span-1">
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700">Save</button>
                    </div>
                </form>
            </div>
            @empty
            <p class="text-gray-400">No leagues found.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
