@extends('layouts.app')

@section('title', 'Prediction Markets - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Prediction Markets</h1>
            <p class="text-gray-600">Enable or disable individual prediction markets and set their confidence thresholds.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="space-y-6">
            @forelse($markets as $market)
            <div class="bg-white rounded-lg shadow-sm border {{ $market->enabled ? 'border-gray-200' : 'border-red-200 bg-red-50/30' }} p-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $market->name }}</h2>
                        <p class="text-sm text-gray-500">Code: {{ $market->code }} · Sort: {{ $market->sort_order }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $market->enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $market->enabled ? 'ENABLED' : 'DISABLED' }}
                        </span>
                        <form method="POST" action="{{ route('admin.predictions.markets.toggle', $market) }}" onsubmit="return confirm('Toggle this market? Existing predictions are preserved.')">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded-lg text-xs font-semibold {{ $market->enabled ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200' }}">
                                {{ $market->enabled ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </div>
                </div>

                @if($market->code === 'correct_score')
                <div class="mb-3 px-4 py-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Correct Score is a high-variance market. Use a higher confidence threshold.
                </div>
                @endif

                <form method="POST" action="{{ route('admin.predictions.markets.settings', $market) }}" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end border-t border-gray-100 pt-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Min Probability</label>
                        <input type="number" name="min_probability" value="{{ $market->min_probability ?? '' }}" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="—">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Min Confidence</label>
                        <input type="number" name="min_confidence" value="{{ $market->min_confidence }}" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Min Sample Size</label>
                        <input type="number" name="minimum_sample_size" value="{{ $market->minimum_sample_size }}" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ $market->sort_order }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700">Save</button>
                    </div>
                    <div class="col-span-2 md:col-span-5 flex items-center gap-2">
                        <label class="inline-flex items-center text-sm text-gray-700">
                            <input type="checkbox" name="homepage_enabled" value="1" {{ $market->homepage_enabled ? 'checked' : '' }} class="rounded border-gray-300 mr-1"> Homepage
                        </label>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $market->gate_status === 'approved' ? 'bg-green-100 text-green-800' : ($market->gate_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700') }}">
                            Gate: {{ strtoupper($market->gate_status ?? 'none') }}
                        </span>
                    </div>
                </form>
            </div>
            @empty
            <p class="text-gray-400">No markets found.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
