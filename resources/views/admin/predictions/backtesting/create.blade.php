@extends('layouts.app')

@section('title', 'New Backtest - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">New Backtest</h1>
            <p class="text-gray-600">Evaluate the model against stored historical matches using walk-forward evaluation (no future-data leakage).</p>
        </div>

        @include('admin.partials.prediction-nav')

        @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.predictions.backtesting.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">League</label>
                <select name="league_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    <option value="">All leagues</option>
                    @foreach($leagues as $league)
                        <option value="{{ $league->api_football_league_id }}" {{ old('league_id') == $league->api_football_league_id ? 'selected' : '' }}>
                            {{ $league->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Season</label>
                <input type="number" name="season" value="{{ old('season', date('Y')) }}" min="2000" max="2100"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="date_start" value="{{ old('date_start') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="date_end" value="{{ old('date_end') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Markets</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($markets as $market)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="markets[]" value="{{ $market->code }}"
                                   {{ in_array($market->code, old('markets', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            {{ $market->name }}
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-1">Leave empty to backtest all markets.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Confidence</label>
                    <input type="number" name="min_confidence" value="{{ old('min_confidence', 0) }}" min="0" max="100"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Probability (%)</label>
                    <input type="number" name="min_probability" value="{{ old('min_probability', 0) }}" min="0" max="100" step="0.01"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Model Version</label>
                <select name="model_version" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    @foreach($models as $model)
                        <option value="{{ $model->version }}" {{ old('model_version', $activeVersion) == $model->version ? 'selected' : '' }}>
                            {{ $model->name }} — {{ $model->version }} {{ $model->active ? '(active)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.predictions.backtesting.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                <button type="submit" class="px-6 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
                    <i class="fas fa-play mr-1"></i> Start Backtest
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
