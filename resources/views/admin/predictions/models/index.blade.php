@extends('layouts.app')

@section('title', 'Prediction Models - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $markets = ['1x2', 'draw', 'double_chance', 'over_1_5', 'over_2_5', 'btts', 'correct_score'];
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Prediction Models</h1>
            <p class="text-gray-600">Compare model versions. v1.1.0 is a candidate — it is not active until explicitly validated and enabled.</p>
        </div>

        @include('admin.partials.prediction-nav')

        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Registered models -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Registered Models</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($models as $model)
                <div class="bg-gray-50 rounded-lg p-4 border {{ $model->status === 'active' ? 'border-green-300' : 'border-gray-200' }}">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-semibold text-gray-900">{{ $model->name }}</p>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'shadow' => 'bg-blue-100 text-blue-800',
                                'approved' => 'bg-emerald-100 text-emerald-800',
                                'candidate' => 'bg-amber-100 text-amber-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'retired' => 'bg-gray-200 text-gray-700',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$model->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ strtoupper($model->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ $model->version }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $model->description }}</p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if(in_array($model->status, ['shadow', 'candidate']))
                        <form method="POST" action="{{ route('admin.predictions.models.approve', $model) }}">
                            @csrf
                            <button class="px-3 py-1 rounded text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.predictions.models.reject', $model) }}">
                            @csrf
                            <button class="px-3 py-1 rounded text-xs font-semibold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100">Reject</button>
                        </form>
                        @endif

                        @if($model->status === 'approved')
                        <form method="POST" action="{{ route('admin.predictions.models.activate', $model) }}" onsubmit="return confirm('Activate {{ $model->version }}? The current ACTIVE model will be retired.');">
                            @csrf
                            <button class="px-3 py-1 rounded text-xs font-semibold bg-green-600 text-white hover:bg-green-700">Activate</button>
                        </form>
                        @endif

                        @if(in_array($model->status, ['active', 'approved']))
                        <form method="POST" action="{{ route('admin.predictions.models.retire', $model) }}" onsubmit="return confirm('Retire {{ $model->version }}?');">
                            @csrf
                            <button class="px-3 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200">Retire</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Per-market comparison across versions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Per-Market Performance by Version</h2>
                <a href="{{ route('admin.predictions.models.compare') }}" class="text-sm text-green-600 font-semibold hover:underline">Side-by-side compare</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            @foreach(array_keys($versions) as $version)
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $version }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($markets as $market)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $market }}</td>
                            @foreach($versions as $version => $data)
                                @php $m = $data['by_market'][$market] ?? null; @endphp
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if($m && $m['resolved'] > 0)
                                        {{ $fmt($m['accuracy'], '%') }}
                                        <span class="text-xs text-gray-400">(n={{ $m['resolved'] }})</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50">
                            <td class="px-4 py-3 text-sm font-bold text-gray-900">Overall</td>
                            @foreach($versions as $version => $data)
                                @php $o = $data['overview']; @endphp
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                    {{ $fmt($o['accuracy'], '%') }}
                                    <span class="text-xs text-gray-400">(n={{ $o['resolved'] }})</span>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="px-6 py-3 text-xs text-gray-500">Sample sizes shown. Values below {{ $minimumSample }} resolved predictions should not be treated as reliable.</p>
        </div>
    </div>
</div>
@endsection
