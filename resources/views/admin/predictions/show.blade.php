@extends('layouts.app')

@section('title', 'Prediction Detail - Admin')

@section('content')
@php
    $snapshot = $prediction->features->first()?->features ?? [];
    $selectionOptions = match($prediction->market_code) {
        '1x2' => ['home' => 'Home', 'draw' => 'Draw', 'away' => 'Away'],
        'draw' => ['draw' => 'Draw'],
        'double_chance' => ['1x' => '1X', 'x2' => 'X2', '12' => '12'],
        'over_1_5' => ['over_1_5' => 'Over 1.5', 'under_1_5' => 'Under 1.5'],
        'over_2_5' => ['over_2_5' => 'Over 2.5', 'under_2_5' => 'Under 2.5'],
        'btts' => ['yes' => 'Yes', 'no' => 'No'],
        default => [],
    };
    $modelSignalNames = [
        'poisson' => 'Poisson', 'form' => 'Form', 'home_away' => 'Home/Away',
        'team_strength' => 'Team Strength', 'api_football' => 'API-Football', 'odds' => 'Odds',
    ];
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <a href="{{ route('admin.predictions.list') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to predictions
        </a>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ $prediction->fixture?->home_team ?? '—' }} vs {{ $prediction->fixture?->away_team ?? '—' }}</h1>
            <p class="text-gray-600">
                {{ $prediction->league?->name ?? '—' }} ·
                @if($prediction->fixture?->match_date){{ $prediction->fixture->match_date->format('M d, Y — H:i') }}@endif
            </p>
        </div>

        @include('admin.partials.prediction-nav')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: prediction + override -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Model prediction -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Model Prediction</h2>
                    <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div><dt class="text-gray-500 uppercase text-xs">Market</dt><dd class="font-semibold">{{ $prediction->market_code }}</dd></div>
                        <div><dt class="text-gray-500 uppercase text-xs">Model Selection</dt><dd class="font-semibold">{{ $prediction->selection }}</dd></div>
                        <div><dt class="text-gray-500 uppercase text-xs">Probability</dt><dd class="font-semibold">{{ $prediction->probability }}%</dd></div>
                        <div><dt class="text-gray-500 uppercase text-xs">Confidence</dt><dd class="font-semibold">{{ $prediction->confidence }}</dd></div>
                        <div><dt class="text-gray-500 uppercase text-xs">Data Quality</dt><dd class="font-semibold">{{ $prediction->data_quality_score }}</dd></div>
                        <div><dt class="text-gray-500 uppercase text-xs">Model Version</dt><dd class="font-semibold">{{ $prediction->model_version }}</dd></div>
                        <div><dt class="text-gray-500 uppercase text-xs">Status</dt>
                            <dd class="font-semibold">
                                @if($prediction->status === 'no_bet')
                                    <span class="text-amber-700">NO BET</span>
                                    <span class="block text-xs font-normal text-gray-500">{{ $prediction->no_bet_reason }}</span>
                                @else
                                    {{ strtoupper($prediction->status) }}
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <h3 class="text-md font-semibold text-gray-900 mt-6 mb-2">Expected Goals</h3>
                    <p class="text-sm text-gray-700">
                        Home: <strong>{{ $snapshot['expected_home_goals'] ?? '—' }}</strong> ·
                        Away: <strong>{{ $snapshot['expected_away_goals'] ?? '—' }}</strong>
                    </p>
                </div>

                <!-- Admin override -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Admin Override</h2>
                    @if($prediction->is_overridden)
                        <p class="text-sm text-amber-700 mb-4">
                            Currently overridden: <strong>{{ $prediction->admin_selection }}</strong>
                            ({{ $prediction->override_reason }})
                        </p>
                    @endif

                    <form method="POST" action="{{ route('admin.predictions.override', $prediction) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Selection</label>
                                @if($prediction->market_code === 'correct_score')
                                    <input type="text" name="selection" value="{{ $prediction->admin_selection ?? $prediction->selection }}" placeholder="e.g. 2-1" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                @else
                                    <select name="selection" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        @foreach($selectionOptions as $value => $label)
                                            <option value="{{ $value }}" {{ ($prediction->admin_selection ?? $prediction->selection) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Probability (optional)</label>
                                <input type="number" step="0.1" name="probability" value="{{ $prediction->probability }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reason (required)</label>
                                <input type="text" name="reason" placeholder="e.g. Late injury news" minlength="5" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-green-700">Save Override</button>
                            @if($prediction->is_overridden)
                                <button type="submit" form="revert-form" class="text-sm text-gray-600 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50">Revert to AI</button>
                            @endif
                        </div>
                    </form>
                    <form id="revert-form" method="POST" action="{{ route('admin.predictions.revert', $prediction) }}" onsubmit="return confirm('Revert to AI prediction?')">
                        @csrf
                    </form>
                </div>

                <!-- Audit history -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Audit History</h2>
                    @forelse($prediction->overrides as $override)
                        <div class="py-2 border-b border-gray-50 text-sm">
                            <span class="text-gray-500">{{ $override->original_selection }}</span>
                            <i class="fas fa-arrow-right mx-2 text-gray-300"></i>
                            <span class="font-semibold">{{ $override->new_selection }}</span>
                            <span class="text-gray-400"> — {{ $override->reason }} ({{ $override->admin?->name ?? 'Admin' }}, {{ $override->created_at->format('Y-m-d H:i') }})</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No override history.</p>
                    @endforelse
                </div>
            </div>

            <!-- Right: model info + features + actions -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Model Information</h2>
                    @if(!empty($snapshot['model_signals']))
                        <table class="w-full text-sm">
                            @foreach($snapshot['model_signals'] as $signal)
                                @php
                                    $top = 'home';
                                    if ($signal['draw'] >= $signal['home'] && $signal['draw'] >= $signal['away']) $top = 'draw';
                                    if ($signal['away'] >= $signal['home'] && $signal['away'] >= $signal['draw']) $top = 'away';
                                @endphp
                                <tr class="border-b border-gray-50">
                                    <td class="py-2 text-gray-600">{{ $modelSignalNames[$signal['name']] ?? $signal['name'] }}</td>
                                    <td class="py-2 text-right font-semibold {{ $signal['available'] ? 'text-gray-900' : 'text-gray-400' }}">
                                        {{ $signal['available'] ? ucfirst($top) : 'unavailable' }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        <p class="text-sm text-gray-700 mt-3">Model Agreement: <strong>{{ $snapshot['model_agreement'] ?? '—' }}%</strong></p>
                    @else
                        <p class="text-sm text-gray-400">No model signals stored.</p>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Feature Snapshot</h2>
                    @if(!empty($snapshot))
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-gray-500 text-xs uppercase">Home Form</dt><dd class="font-semibold">{{ $snapshot['home_form_score'] ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500 text-xs uppercase">Away Form</dt><dd class="font-semibold">{{ $snapshot['away_form_score'] ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500 text-xs uppercase">Home Attack</dt><dd class="font-semibold">{{ $snapshot['home_attack_strength'] ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500 text-xs uppercase">Away Attack</dt><dd class="font-semibold">{{ $snapshot['away_attack_strength'] ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500 text-xs uppercase">Home Defense</dt><dd class="font-semibold">{{ $snapshot['home_defense_strength'] ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500 text-xs uppercase">Away Defense</dt><dd class="font-semibold">{{ $snapshot['away_defense_strength'] ?? '—' }}</dd></div>
                            <div><dt class="text-gray-500 text-xs uppercase">Data Quality</dt><dd class="font-semibold">{{ $snapshot['data_quality'] ?? '—' }}</dd></div>
                        </dl>
                    @else
                        <p class="text-sm text-gray-400">No feature snapshot stored.</p>
                    @endif
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-3">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Actions</h2>

                    @if($prediction->locked_at)
                        <form method="POST" action="{{ route('admin.predictions.unlock', $prediction) }}">
                            @csrf
                            <button class="w-full bg-gray-100 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200">Unlock</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.predictions.lock', $prediction) }}" onsubmit="return confirm('Lock this prediction? Regeneration will not overwrite it.')">
                            @csrf
                            <button class="w-full bg-gray-100 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200">Lock</button>
                        </form>
                    @endif

                    @if($prediction->status === 'published')
                        <form method="POST" action="{{ route('admin.predictions.unpublish', $prediction) }}" onsubmit="return confirm('Unpublish this prediction?')">
                            @csrf
                            <button class="w-full bg-amber-50 text-amber-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-100">Unpublish</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.predictions.publish', $prediction) }}" onsubmit="return confirm('Publish this prediction?')">
                            @csrf
                            <button class="w-full bg-green-50 text-green-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-100">Publish</button>
                        </form>
                    @endif

                    @if($prediction->featured)
                        <form method="POST" action="{{ route('admin.predictions.unfeature', $prediction) }}">
                            @csrf
                            <button class="w-full bg-purple-50 text-purple-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-100">Unfeature</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.predictions.feature', $prediction) }}" class="flex gap-2">
                            @csrf
                            <input type="number" name="featured_priority" placeholder="Priority" value="0" min="0" class="w-24 border border-gray-300 rounded-lg px-2 py-2 text-sm">
                            <input type="hidden" name="admin_featured" value="1">
                            <button class="flex-1 bg-purple-50 text-purple-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-purple-100">Feature</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
