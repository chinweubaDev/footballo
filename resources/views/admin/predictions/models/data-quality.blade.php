@extends('layouts.app')

@section('title', 'Data Quality - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Data Quality Report</h1>
            <p class="text-gray-600">Which model inputs are actually available, and why the backtest data-quality score is capped.</p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Feature Availability</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Feature</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Coverage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($report['features'] as $feature)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $feature['feature'] }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $feature['available'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $feature['available'] ? 'YES' : 'NO' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $feature['source'] }}</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-700">{{ $feature['coverage'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Data-Quality Score Distribution (backtest)</h2>
            @if($report['distribution'])
                @php $d = $report['distribution']; @endphp
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $d['count'] }}</p><p class="text-xs text-gray-500 uppercase">Predictions</p></div>
                    <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $d['min'] }}</p><p class="text-xs text-gray-500 uppercase">Min score</p></div>
                    <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $d['max'] }}</p><p class="text-xs text-gray-500 uppercase">Max score</p></div>
                    <div class="bg-gray-50 rounded-lg p-4"><p class="text-xl font-bold text-gray-900">{{ $d['avg'] }}</p><p class="text-xs text-gray-500 uppercase">Avg score</p></div>
                </div>
            @else
                <p class="text-sm text-gray-400">No backtest data yet.</p>
            @endif
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
            <i class="fas fa-info-circle mr-2"></i>{{ $report['note'] }}
        </div>
    </div>
</div>
@endsection
