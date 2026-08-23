@extends('layouts.app')

@section('title', 'League × Market Gates - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">League × Market Gates</h1>
            <p class="text-gray-600">Most-specific gate tier. Precedence: league × market → market → league → global.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Gate Matrix</h2>
                <p class="text-xs text-gray-500">Each cell: enable/disable + optional probability/confidence override. Empty override = fall through to the next tier.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            @foreach($markets as $market)
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ $market->code }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($leagues as $league)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 whitespace-nowrap">{{ $league->name }}</td>
                            @foreach($markets as $market)
                                @php $gate = $matrix[$league->api_football_league_id][$market->code] ?? null; @endphp
                                <td class="px-3 py-3 text-center align-top">
                                    <form method="POST" action="{{ route('admin.predictions.settings.matrix.update', [$league, $market->code]) }}" class="inline-flex flex-col gap-1 items-center">
                                        @csrf
                                        <label class="flex items-center gap-1 text-xs text-gray-600">
                                            <input type="checkbox" name="enabled" value="1" {{ !$gate || $gate->enabled ? 'checked' : '' }}>
                                            enabled
                                        </label>
                                        <input type="number" name="min_probability" min="0" max="100" value="{{ $gate?->min_probability ?? '' }}"
                                               placeholder="P" class="w-16 rounded border-gray-300 text-xs text-center" title="Min probability">
                                        <input type="number" name="min_confidence" min="0" max="100" value="{{ $gate?->min_confidence ?? '' }}"
                                               placeholder="C" class="w-16 rounded border-gray-300 text-xs text-center" title="Min confidence">
                                        <button type="submit" class="px-2 py-1 rounded bg-green-600 text-white text-[10px] font-semibold hover:bg-green-700">Save</button>
                                    </form>
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
