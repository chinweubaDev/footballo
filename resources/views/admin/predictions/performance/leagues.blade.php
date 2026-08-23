@extends('layouts.app')

@section('title', 'League Performance (Live) - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $evidenceBadge = fn ($label) => match($label) {
        'STRONGER EVIDENCE' => 'bg-green-100 text-green-800',
        'MEANINGFUL' => 'bg-blue-100 text-blue-800',
        'PRELIMINARY' => 'bg-amber-100 text-amber-800',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">League Performance (Live)</h1>
            <p class="text-gray-600">Resolved live predictions per league, split by model version.</p>
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
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ECE</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evidence</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($byModel[$version] ?? [] as $l)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $l['league_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $l['resolved'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-700">{{ $l['won'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-red-700">{{ $l['lost'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['accuracy'] ?? null, '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['brier_score'] ?? null, '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($l['calibration_error'] ?? null) }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $evidenceBadge($l['evidence']) }}">{{ $l['evidence'] }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-6 py-8 text-center text-gray-400">No resolved predictions for this model.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
