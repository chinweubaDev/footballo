@props(['fixture'])

<div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            @php $leagueLogo = $fixture->league?->logo ?? $fixture->league_logo ?? null; @endphp
            @if($leagueLogo)
                <img src="{{ $leagueLogo }}" alt="" class="w-8 h-8 object-contain">
            @else
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center"><i class="fas fa-trophy text-slate-400 text-sm"></i></div>
            @endif
            <div>
                <span class="block text-xs font-bold text-primary-600 uppercase tracking-wide">{{ $fixture->league?->name ?? $fixture->league_name ?? '—' }}</span>
                <span class="block text-xs text-slate-500">{{ $fixture->match_date?->format('D, M d — H:i') }}</span>
            </div>
        </div>
        <a href="{{ route('predictions.fixture', ['league' => $fixture->league?->slug ?? 'predictions', 'fixture' => $fixture->slug ?? $fixture->id]) }}" class="text-xs font-bold text-primary-600 hover:underline whitespace-nowrap">View Analysis</a>
    </div>

    <div class="flex items-center justify-between gap-4 mb-5">
        <div class="text-center flex-1">
            @if($fixture->home_team_logo)<img src="{{ $fixture->home_team_logo }}" alt="" class="w-10 h-10 object-contain mx-auto mb-1">@endif
            <p class="text-sm font-bold text-slate-900">{{ $fixture->home_team }}</p>
        </div>
        <span class="text-slate-300 font-black text-xs">VS</span>
        <div class="text-center flex-1">
            @if($fixture->away_team_logo)<img src="{{ $fixture->away_team_logo }}" alt="" class="w-10 h-10 object-contain mx-auto mb-1">@endif
            <p class="text-sm font-bold text-slate-900">{{ $fixture->away_team }}</p>
        </div>
    </div>

    @if($fixture->predictions->isNotEmpty())
        <div class="space-y-3">
            @foreach($fixture->predictions as $prediction)
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                    <div>
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-wide">{{ $prediction->category ?? strtoupper($prediction->market_code) }}</span>
                        <span class="text-sm font-extrabold text-slate-900">{{ $prediction->effective_selection }}</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-bold text-slate-900">{{ $prediction->probability }}%</span>
                        <span class="block text-xs text-slate-500">conf {{ $prediction->confidence }}/100</span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-400 text-center py-2">No published predictions.</p>
    @endif
</div>
