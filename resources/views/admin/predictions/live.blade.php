@extends('layouts.app')

@section('title', 'Live Shadow Validation - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Live Shadow Validation</h1>
            <p class="text-gray-600">Production v1.0.0 vs shadow v1.1.0. Shadow predictions are never shown publicly.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            @php
                $cards = [
                    ['label' => "Today's Fixtures", 'value' => $counts['fixtures_today'], 'color' => 'text-gray-900'],
                    ['label' => 'Generated', 'value' => $counts['generated'], 'color' => 'text-gray-900'],
                    ['label' => 'Published', 'value' => $counts['published'], 'color' => 'text-green-700'],
                    ['label' => 'No Bet', 'value' => $counts['no_bet'], 'color' => 'text-red-700'],
                    ['label' => 'Shadow (v1.1)', 'value' => $counts['shadow'], 'color' => 'text-indigo-700'],
                    ['label' => 'Locked', 'value' => $counts['locked'], 'color' => 'text-amber-700'],
                    ['label' => 'Settled', 'value' => $counts['settled'], 'color' => 'text-gray-900'],
                    ['label' => 'Overridden', 'value' => $counts['overridden'], 'color' => 'text-amber-700'],
                    ['label' => 'Pending Review', 'value' => $counts['pending_review'], 'color' => 'text-gray-700'],
                    ['label' => 'Rejected', 'value' => $counts['rejected'], 'color' => 'text-red-700'],
                ];
            @endphp
            @foreach($cards as $card)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="text-xs font-medium text-gray-500 uppercase">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
            </div>
            @endforeach
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Resolved Predictions by Model</h2>
                <p class="text-xs text-gray-500">Minimum sample thresholds: 50 preliminary, 100 meaningful, 500 stronger evidence.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Resolved</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wins</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Losses</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Voids</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($byModel as $row)
                        @php
                            $resolved = $row->won + $row->lost;
                            $accuracy = $resolved > 0 ? round($row->won / $resolved * 100, 2) : null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $row->model_version }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $resolved }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-700">{{ $row->won }}</td>
                            <td class="px-4 py-3 text-sm text-right text-red-700">{{ $row->lost }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $row->void }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $accuracy === null ? '—' : $accuracy.'%' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No resolved live predictions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
