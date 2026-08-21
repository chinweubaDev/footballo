@extends('layouts.app')

@section('title', 'Publication Gates - Admin')

@section('content')
@php
    $fmt = fn ($v, $suffix = '', $decimals = 2) => $v === null ? '—' : number_format((float) $v, $decimals).$suffix;
    $markets = $report['markets'] ?? [];
    $source = $report['source'] ?? [];
    $minSample = $report['minimum_sample_size'] ?? 100;
    $insufficient = $report['insufficient_sample_threshold'] ?? 50;

    $badge = fn ($status) => match ($status) {
        'CURRENT' => 'bg-green-100 text-green-800',
        'PROMISING' => 'bg-emerald-100 text-emerald-800',
        'WEAK' => 'bg-red-100 text-red-700',
        default => 'bg-amber-100 text-amber-700',
    };

    // Render a compact accuracy-vs-coverage scatter for a market's grid.
    $chart = function (array $grid, ?int $recProb, ?int $recConf) {
        $w = 360; $h = 220; $pad = 34;
        $svg = '<svg viewBox="0 0 '.$w.' '.$h.'" class="w-full h-40" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<line x1="'.$pad.'" y1="'.($h-$pad).'" x2="'.($w-8).'" y2="'.($h-$pad).'" stroke="#cbd5e1"/>';
        $svg .= '<line x1="'.$pad.'" y1="'.$pad.'" x2="'.$pad.'" y2="'.($h-$pad).'" stroke="#cbd5e1"/>';
        foreach ($grid as $p) {
            $cov = (float) ($p['coverage_percent'] ?? 0);
            $acc = (float) ($p['accuracy'] ?? 0);
            if ($acc === null || $cov === null) continue;
            $cx = $pad + ($cov / 100) * ($w - $pad - 12);
            $cy = ($h - $pad) - ($acc / 100) * ($h - 2 * $pad);
            $isCurrent = (int) $p['min_probability'] === 70 && (int) $p['min_confidence'] === 75;
            $isRec = $recProb !== null && (int) $p['min_probability'] === $recProb && (int) $p['min_confidence'] === $recConf;
            $fill = $isCurrent ? '#ef4444' : ($isRec ? '#16a34a' : '#94a3b8');
            $r = ($isCurrent || $isRec) ? 4 : 2.5;
            $svg .= '<circle cx="'.round($cx,1).'" cy="'.round($cy,1).'" r="'.$r.'" fill="'.$fill.'"><title>'.htmlspecialchars($p['min_probability'].'/'.$p['min_confidence'].' acc '.$p['accuracy'].'% cov '.$p['coverage_percent'].'% n='.$p['predictions'], ENT_QUOTES).'</title></circle>';
        }
        $svg .= '<text x="'.($pad+8).'" y="12" font-size="9" fill="#64748b">Accuracy ↑</text>';
        $svg .= '<text x="'.($w-120).'" y="'.($h-6).'" font-size="9" fill="#64748b">Coverage →</text>';
        $svg .= '</svg>';
        return $svg;
    };
@endphp

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Publication Gate Optimization</h1>
            <p class="text-gray-600">Per-market probability × confidence threshold sweep. Thresholds are never applied automatically — an admin must approve them.</p>
            @if($source['kind'] ?? null)
            <p class="text-xs text-gray-500 mt-2">
                Data source: <span class="font-semibold">{{ $source['kind'] }}</span>
                @if(!empty($source['run_id'])) (run #{{ $source['run_id'] }})@endif
                @if(!empty($source['model_version'])) · model {{ $source['model_version'] }}@endif
                @if(!empty($source['league_id'])) · league {{ $source['league_id'] }}@endif
                @if(!empty($source['season'])) · season {{ $source['season'] }}@endif
            </p>
            @endif
        </div>

        @include('admin.partials.prediction-nav')

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Market Gates</h2>
                <p class="text-xs text-gray-500">Sample labels: &lt;{{ $insufficient }} = INSUFFICIENT SAMPLE · &lt;{{ $minSample }} = LOW SAMPLE · ≥{{ $minSample }} = SUFFICIENT SAMPLE.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Market</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Curr Prob</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Curr Conf</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rec Prob</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rec Conf</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">n</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Accuracy</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Coverage</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Brier</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">95% CI</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($markets as $m)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-3 py-3 text-sm font-semibold text-gray-900">
                                {{ $m['name'] }}
                                @if(!$m['enabled'])<span class="text-xs text-red-500">(disabled)</span>@endif
                                <div class="text-xs font-normal text-gray-400">{{ $m['market'] }}</div>
                            </td>
                            <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $fmt($m['current_min_probability'], '', 0) }}</td>
                            <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $fmt($m['current_min_confidence'], '', 0) }}</td>
                            <td class="px-3 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($m['recommended_min_probability'], '', 0) }}</td>
                            <td class="px-3 py-3 text-sm text-right font-semibold text-gray-900">{{ $fmt($m['recommended_min_confidence'], '', 0) }}</td>
                            <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $fmt($m['sample_size'], '', 0) }}</td>
                            <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $fmt($m['accuracy'], '%') }}</td>
                            <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $fmt($m['coverage_percent'], '%') }}</td>
                            <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $fmt($m['brier_score'], '', 4) }}</td>
                            <td class="px-3 py-3 text-sm text-right text-gray-500">{{ $fmt($m['ci_lower'], '%').'–'.$fmt($m['ci_upper'], '%') }}</td>
                            <td class="px-3 py-3 text-sm text-left"><span class="px-2 py-1 rounded text-xs font-semibold {{ $badge($m['status']) }}">{{ $m['status'] }}</span></td>
                            <td class="px-3 py-3 text-sm">
                                @if($m['recommended_min_probability'] !== null)
                                <form method="POST" action="{{ route('admin.predictions.gates.approve', $m['category']) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="min_probability" value="{{ $m['recommended_min_probability'] }}">
                                    <input type="hidden" name="min_confidence" value="{{ $m['recommended_min_confidence'] }}">
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700">Approve</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.predictions.gates.reject', $m['category']) }}" class="inline-flex">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-300">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="bg-gray-50/60">
                            <td colspan="12" class="px-3 py-3">
                                @if($m['reason'])<p class="text-xs text-gray-500 mb-2">{{ $m['reason'] }}</p>@endif
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Accuracy vs Coverage</p>
                                        {!! $chart($m['grid'], $m['recommended_min_probability'], $m['recommended_min_confidence']) !!}
                                        <p class="text-[10px] text-gray-400">● red = current (70/75) · ● green = recommended</p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Grid</p>
                                        <table class="min-w-full divide-y divide-gray-100 text-xs">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-2 py-1 text-left">P/C</th>
                                                    <th class="px-2 py-1 text-right">n</th>
                                                    <th class="px-2 py-1 text-right">Acc</th>
                                                    <th class="px-2 py-1 text-right">Cov</th>
                                                    <th class="px-2 py-1 text-right">Brier</th>
                                                    <th class="px-2 py-1 text-left">Sample</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($m['grid'] as $p)
                                                @php $isCur = (int)$p['min_probability'] === 70 && (int)$p['min_confidence'] === 75; @endphp
                                                <tr class="{{ $isCur ? 'bg-red-50' : '' }}">
                                                    <td class="px-2 py-1 {{ $isCur ? 'font-bold' : '' }}">{{ $p['min_probability'] }}/{{ $p['min_confidence'] }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $p['predictions'] }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($p['accuracy'], '%') }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($p['coverage_percent'], '%') }}</td>
                                                    <td class="px-2 py-1 text-right">{{ $fmt($p['brier_score'], '', 3) }}</td>
                                                    <td class="px-2 py-1">{{ $p['sample_label'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="12" class="px-6 py-8 text-center text-gray-400">No market data. Run a backtest first.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
