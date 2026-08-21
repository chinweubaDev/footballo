@extends('layouts.app')

@section('title', 'Shadow Mode - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $markets = ['1x2', 'draw', 'double_chance', 'over_1_5', 'over_2_5', 'btts', 'correct_score'];
    $active = $data['active'] ?? null;
    $shadow = $data['shadow'] ?? null;
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Shadow Mode</h1>
            <p class="text-gray-600">Compare the ACTIVE production model against the SHADOW candidate on resolved predictions.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Recommendation</h2>
            <p class="text-lg font-semibold {{ str_contains($data['recommendation'] ?? '', 'improvement') ? 'text-green-700' : 'text-gray-700' }}">
                {{ $data['recommendation'] }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Minimum shadow sample: {{ $data['minimum_shadow'] }} resolved. The system never auto-activates.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            @foreach([['ACTIVE', $active], ['SHADOW', $shadow]] as [$label, $model])
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $label }} Model</h2>
                @if($model && $model['performance'])
                    @php $o = $model['performance']['overview']; @endphp
                    <p class="text-sm text-gray-600 mb-2">{{ $model['model']['name'] }} — {{ $model['model']['version'] }}</p>
                    <div class="grid grid-cols-2 gap-2 text-sm text-gray-700 mb-4">
                        <span>Resolved:</span><span class="text-right font-semibold">{{ $o['resolved'] }}</span>
                        <span>Wins / Losses:</span><span class="text-right">{{ $o['won'] }} / {{ $o['lost'] }}</span>
                        <span>Accuracy:</span><span class="text-right font-semibold">{{ $fmt($o['accuracy'], '%') }}</span>
                        <span>Brier:</span><span class="text-right">{{ $fmt($o['brier_score'], '', 4) }}</span>
                        <span>Log loss:</span><span class="text-right">{{ $fmt($o['log_loss'], '', 4) }}</span>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Acc</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">n</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($markets as $market)
                            @php $m = $model['performance']['by_market'][$market] ?? null; @endphp
                            <tr>
                                <td class="px-3 py-1.5 text-sm text-gray-900">{{ $market }}</td>
                                <td class="px-3 py-1.5 text-sm text-right text-gray-700">{{ $m ? $fmt($m['accuracy'], '%') : '—' }}</td>
                                <td class="px-3 py-1.5 text-sm text-right text-gray-500">{{ $m['resolved'] ?? '—' }}</td>
                                <td class="px-3 py-1.5 text-sm text-right text-gray-700">{{ $m ? $fmt($m['brier_score'], '', 3) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-sm text-gray-400">No data yet.</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
