@extends('layouts.app')

@section('title', 'Publication Candidates - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $badge = fn ($s) => match (strtoupper($s)) {
        'APPROVED' => 'bg-green-100 text-green-800',
        'REJECTED' => 'bg-red-100 text-red-700',
        default => 'bg-amber-100 text-amber-700',
    };
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Publication Candidates</h1>
            <p class="text-gray-600">League × Market combinations that clear the evidence bar. Marked CANDIDATE by data only — they are never enabled automatically and require explicit admin approval.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-8 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Season</label>
                <input type="number" name="season" value="{{ $filters['season'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="2025">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase mb-1">Model</label>
                <select name="model" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach($models as $m)
                        <option value="{{ $m }}" {{ ($filters['model'] ?? null) === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700">Filter</button>
            </div>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Candidates</h2>
                <p class="text-xs text-gray-500">Candidate rule: n ≥ adequate threshold, accuracy ≥ min, Brier ≤ max, and the publication gate clears its own minimums. Status below reflects admin decisions.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">League</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Coverage</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Calibration</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Recommended Gate</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sample Size</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($candidates as $c)
                        <tr class="hover:bg-gray-50 {{ $c['is_candidate'] ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $c['league'] }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $c['market_label'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $c['model'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $fmt($c['accuracy'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($c['coverage'], '%') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($c['brier'], '', 4) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $fmt($c['calibration'], '') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">
                                {{ $c['recommended_gate'] ?? '—' }}
                                @if(isset($c['recommended_gate_n']))
                                    <span class="text-xs text-gray-400">(n={{ $c['recommended_gate_n'] }}, {{ $fmt($c['recommended_gate_accuracy'], '%') }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $c['n'] }} <span class="text-xs text-gray-400">({{ $c['sample_status'] }})</span></td>
                            <td class="px-4 py-3 text-sm"><span class="px-2 py-1 rounded text-xs font-semibold {{ $badge($c['status']) }}">{{ $c['status'] }}</span></td>
                            <td class="px-4 py-3 text-sm">
                                @if(strtoupper($c['status']) !== 'APPROVED')
                                <form method="POST" action="{{ route('admin.predictions.validation.candidates.decide') }}" class="inline-flex gap-1">
                                    @csrf
                                    <input type="hidden" name="league_id" value="{{ $c['league_id'] }}">
                                    <input type="hidden" name="market_code" value="{{ $c['market'] }}">
                                    <input type="hidden" name="model_version" value="{{ $c['model'] }}">
                                    <button name="status" value="approved" class="px-2 py-1 rounded bg-green-600 text-white text-xs font-semibold hover:bg-green-700">Approve</button>
                                    <button name="status" value="rejected" class="px-2 py-1 rounded bg-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-300">Reject</button>
                                </form>
                                @else
                                <span class="text-xs text-gray-400">approved</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11" class="px-6 py-8 text-center text-gray-400">No candidates yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
