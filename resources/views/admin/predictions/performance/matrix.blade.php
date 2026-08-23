@extends('layouts.app')

@section('title', 'League × Market Matrix (Live) - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">League × Market Matrix (Live)</h1>
            <p class="text-gray-600">League × Market × Model with sample sizes. Low-sample cells are not ranked as best/worst.</p>
        </div>

        @include('admin.partials.prediction-nav')

        @foreach($versions as $version)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">{{ $version }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ECE</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $any = false; @endphp
                        @foreach($byModel[$version] ?? [] as $league)
                            @foreach($league['markets'] as $m)
                            @php $any = true; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $league['league_name'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $m['market_code'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $m['resolved'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-700">{{ $m['won'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-red-700">{{ $m['lost'] }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['accuracy'] ?? null, '%') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['brier_score'] ?? null, '', 4) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($m['calibration_error'] ?? null) }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                        @if(!$any)
                        <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No resolved predictions for this model.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
