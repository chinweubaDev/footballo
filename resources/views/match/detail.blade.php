@extends('layouts.app')

@section('title', $fixture->home_team . ' vs ' . $fixture->away_team . ' Predictions & Betting Tips')
@section('meta_description', 'Expert prediction, betting odds, H2H stats, and analysis for ' . $fixture->home_team . ' vs ' . $fixture->away_team . ' — ' . $fixture->league_name)

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    {{-- Hero Scoreboard --}}
    <section class="relative bg-slate-900 pt-20 pb-16 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #22c55e 1px, transparent 0); background-size: 30px 30px;"></div>
        </div>
        
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 text-slate-400 text-sm mb-4">
                    <span>{{ $fixture->league_country }}</span>
                    <span>·</span>
                    <span>{{ $fixture->league_name }}</span>
                    @if($fixture->round)
                        <span>·</span>
                        <span>{{ $fixture->round }}</span>
                    @endif
                </div>
            </div>

            {{-- Scoreboard --}}
            <div class="bg-white/5 backdrop-blur border border-white/10 rounded-[3rem] p-8 md:p-12">
                <div class="flex items-center justify-center gap-6 md:gap-12">
                    {{-- Home --}}
                    <div class="text-center flex-1">
                        <img src="{{ $fixture->home_team_logo }}" class="w-20 h-20 md:w-24 md:h-24 object-contain mx-auto mb-4">
                        <h1 class="text-lg md:text-2xl font-black text-white">{{ $fixture->home_team }}</h1>
                    </div>

                    {{-- Score --}}
                    <div class="text-center shrink-0">
                        @if(in_array($fixture->status, ['FT', 'AET', 'PEN']))
                            <div class="text-5xl md:text-7xl font-black text-white tracking-tight mb-2">
                                {{ $fixture->home_goals }} <span class="text-slate-500">–</span> {{ $fixture->away_goals }}
                            </div>
                            <span class="inline-flex items-center px-4 py-1.5 bg-green-500/20 text-green-400 rounded-full text-xs font-black uppercase tracking-widest">
                                <i class="fas fa-check mr-2"></i> Full Time
                            </span>
                        @elseif(in_array($fixture->status, ['LIVE','1H','HT','2H']))
                            <div class="text-5xl md:text-7xl font-black text-white tracking-tight mb-2 animate-pulse">
                                {{ $fixture->home_goals ?? 0 }} <span class="text-red-500">–</span> {{ $fixture->away_goals ?? 0 }}
                            </div>
                            <span class="inline-flex items-center px-4 py-1.5 bg-red-500/20 text-red-400 rounded-full text-xs font-black uppercase tracking-widest">
                                <span class="w-2 h-2 rounded-full bg-red-500 mr-2 animate-ping"></span> LIVE {{ $fixture->status === 'HT' ? '· Half Time' : '' }}
                            </span>
                        @else
                            <div class="text-5xl md:text-7xl font-black text-slate-600 tracking-tight mb-2">–</div>
                            <span class="inline-flex items-center px-4 py-1.5 bg-blue-500/20 text-blue-400 rounded-full text-xs font-black uppercase tracking-widest">
                                <i class="far fa-clock mr-2"></i> {{ $fixture->match_date->format('M d, H:i') }}
                            </span>
                        @endif
                    </div>

                    {{-- Away --}}
                    <div class="text-center flex-1">
                        <img src="{{ $fixture->away_team_logo }}" class="w-20 h-20 md:w-24 md:h-24 object-contain mx-auto mb-4">
                        <h1 class="text-lg md:text-2xl font-black text-white">{{ $fixture->away_team }}</h1>
                    </div>
                </div>
            </div>

            @if($fixture->venue_name)
            <div class="text-center mt-4">
                <span class="text-slate-500 text-sm"><i class="fas fa-map-marker-alt mr-1"></i> {{ $fixture->venue_name }}{{ $fixture->venue_city ? ', ' . $fixture->venue_city : '' }}</span>
            </div>
            @endif
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Events Timeline --}}
                @if($events && count($events) > 0)
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                        <i class="fas fa-history text-primary-500 mr-3"></i> Match Events
                    </h3>
                    <div class="space-y-3">
                        @foreach($events as $event)
                        <div class="flex items-center gap-4 py-2 border-b border-slate-50 last:border-0">
                            <span class="text-xs font-black text-slate-400 w-8">{{ $event['time']['elapsed'] }}'</span>
                            @if($event['type'] === 'Goal')
                                <span class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600"><i class="fas fa-futbol text-xs"></i></span>
                                <div class="flex-1">
                                    <span class="text-sm font-bold text-slate-800">{{ $event['player']['name'] }}</span>
                                    @if($event['assist']['name'])
                                        <span class="text-xs text-slate-400 ml-2">({{ $event['assist']['name'] }})</span>
                                    @endif
                                    <span class="text-[10px] text-slate-400 block">{{ $event['detail'] }}</span>
                                </div>
                            @elseif($event['type'] === 'Card')
                                <span class="w-8 h-8 rounded-full {{ $event['detail'] === 'Yellow Card' ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600' }} flex items-center justify-center">
                                    <i class="fas fa-square text-[8px]"></i>
                                </span>
                                <div class="flex-1">
                                    <span class="text-sm font-bold text-slate-800">{{ $event['player']['name'] }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $event['detail'] }}</span>
                                </div>
                            @else
                                <span class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500"><i class="fas fa-exchange-alt text-xs"></i></span>
                                <div class="flex-1">
                                    <span class="text-sm text-slate-600">{{ $event['player']['name'] }} {{ $event['detail'] }}</span>
                                </div>
                            @endif
                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $event['team']['name'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Prediction Card --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                        <i class="fas fa-robot text-primary-500 mr-3"></i> Prediction & Analysis
                    </h3>
                    @if($prediction)
                        <div class="prose prose-slate max-w-none text-sm leading-relaxed">
                            {!! nl2br(e($prediction['analysis'])) !!}
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
                            <div class="bg-slate-50 rounded-xl p-3 text-center">
                                <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">1X2</span>
                                <span class="text-lg font-black text-slate-900">{{ $prediction['1x2']['pick'] }}</span>
                                <span class="text-xs text-primary-600 font-bold block">{{ $prediction['1x2']['confidence'] }}%</span>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-3 text-center">
                                <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">Over 2.5</span>
                                <span class="text-lg font-black text-slate-900">{{ $prediction['over25']['pick'] }}</span>
                                <span class="text-xs text-primary-600 font-bold block">{{ $prediction['over25']['confidence'] }}%</span>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-3 text-center">
                                <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">BTS</span>
                                <span class="text-lg font-black text-slate-900">{{ $prediction['bts']['pick'] === 'Yes' ? 'GG' : 'NG' }}</span>
                                <span class="text-xs text-primary-600 font-bold block">{{ $prediction['bts']['confidence'] }}%</span>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-3 text-center">
                                <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">Score</span>
                                <span class="text-lg font-black text-slate-900">{{ $prediction['correct_score']['most_likely'] }}</span>
                                <span class="text-xs text-primary-600 font-bold block">Predicted</span>
                            </div>
                        </div>
                    @else
                        <p class="text-slate-500">Prediction data is being generated. Check back shortly.</p>
                    @endif
                </div>

                {{-- H2H --}}
                @if($h2h && count($h2h) > 0)
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                        <i class="fas fa-arrows-left-right text-primary-500 mr-3"></i> Head to Head (Last 5)
                    </h3>
                    <div class="divide-y divide-slate-50">
                        @foreach($h2h as $match)
                        @php
                            $hg = $match['goals']['home'] ?? 0;
                            $ag = $match['goals']['away'] ?? 0;
                            $homeWon = $hg > $ag;
                            $awayWon = $ag > $hg;
                        @endphp
                        <div class="flex items-center py-3 gap-4">
                            <span class="text-xs text-slate-400 w-20">{{ \Carbon\Carbon::parse($match['fixture']['date'])->format('M d, Y') }}</span>
                            <div class="flex items-center gap-2 flex-1">
                                <img src="{{ $match['teams']['home']['logo'] ?? '' }}" class="w-5 h-5 object-contain">
                                <span class="text-sm font-bold {{ $homeWon ? 'text-slate-900' : 'text-slate-500' }}">{{ $match['teams']['home']['name'] }}</span>
                            </div>
                            <span class="text-sm font-black {{ $homeWon || $awayWon ? 'text-slate-900' : 'text-slate-400' }} bg-slate-100 px-2 py-0.5 rounded-lg">{{ $hg }} – {{ $ag }}</span>
                            <div class="flex items-center gap-2 flex-1 justify-end">
                                <span class="text-sm font-bold {{ $awayWon ? 'text-slate-900' : 'text-slate-500' }}">{{ $match['teams']['away']['name'] }}</span>
                                <img src="{{ $match['teams']['away']['logo'] ?? '' }}" class="w-5 h-5 object-contain">
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $match['fixture']['status']['short'] === 'FT' ? 'bg-slate-100 text-slate-500' : 'bg-green-100 text-green-600' }}">
                                {{ $match['fixture']['status']['short'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Lineups --}}
                @if($lineups && count($lineups) > 0)
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-900 mb-6 flex items-center">
                        <i class="fas fa-users text-primary-500 mr-3"></i> Lineups
                    </h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        @foreach($lineups as $lineup)
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-bold text-slate-800">{{ $lineup['team']['name'] }}</span>
                                <span class="text-xs text-slate-400">{{ $lineup['formation'] }}</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($lineup['startXI'] ?? [] as $player)
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">{{ $player['player']['number'] }}</span>
                                    <span class="text-slate-700">{{ $player['player']['name'] }}</span>
                                    <span class="text-[10px] text-slate-400 ml-auto">{{ $player['player']['pos'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Odds --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-900 mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-primary-500 mr-3"></i> Betting Odds
                    </h3>
                    @if($odds && count($odds) > 0)
                        @php $bm = $odds[0]['bookmakers'][0] ?? null; @endphp
                        @if($bm)
                            <div class="text-xs text-slate-400 mb-3">{{ $bm['name'] }}</div>
                            @foreach($bm['bets'] as $bet)
                                @if(in_array($bet['id'], [1,5,8]))
                                <div class="mb-4 last:mb-0">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">{{ $bet['name'] }}</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($bet['values'] as $v)
                                        <span class="px-3 py-1.5 bg-slate-50 rounded-xl text-sm font-bold {{ $v['value'] === 'Home' || $v['value'] === 'Yes' || str_contains($v['value'], 'Over') ? 'text-slate-900' : 'text-slate-500' }}">
                                            {{ $v['value'] }} <span class="text-primary-600 ml-1">{{ $v['odd'] }}</span>
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        @endif
                    @else
                        <p class="text-slate-400 text-sm">Odds not available yet.</p>
                    @endif
                </div>

                {{-- Standings --}}
                @if($standings && count($standings) > 0)
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-900 mb-4 flex items-center">
                        <i class="fas fa-table text-primary-500 mr-3"></i> Standings
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs responsive-table">
                            <thead class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="py-2 text-left">#</th>
                                    <th class="py-2 text-left">Team</th>
                                    <th class="py-2 text-right">P</th>
                                    <th class="py-2 text-right">GD</th>
                                    <th class="py-2 text-right">Pts</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @php $shown = 0; @endphp
                                @foreach($standings[0]['league']['standings'][0] ?? [] as $team)
                                    @if($shown < 10)
                                    @php
                                        $isHome = ($team['team']['id'] ?? 0) == $fixture->home_team_id;
                                        $isAway = ($team['team']['id'] ?? 0) == $fixture->away_team_id;
                                    @endphp
                                    <tr class="{{ $isHome || $isAway ? 'bg-primary-50 font-bold' : '' }} hover:bg-slate-50">
                                        <td class="py-2" data-label="#">{{ $team['rank'] }}</td>
                                        <td class="py-2 flex items-center gap-2" data-label="Team">
                                            <img src="{{ $team['team']['logo'] }}" class="w-4 h-4 object-contain">
                                            <span class="truncate max-w-[100px] {{ $isHome || $isAway ? 'text-primary-700' : '' }}">{{ $team['team']['name'] }}</span>
                                        </td>
                                        <td class="py-2 text-right" data-label="P">{{ $team['all']['played'] }}</td>
                                        <td class="py-2 text-right" data-label="GD">{{ $team['goalsDiff'] }}</td>
                                        <td class="py-2 text-right font-black" data-label="Pts">{{ $team['points'] }}</td>
                                    </tr>
                                    @php $shown++; @endphp
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Quick Match Info --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-900 mb-4">Match Info</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Date</span>
                            <span class="font-bold text-slate-700">{{ $fixture->match_date->format('F j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Time</span>
                            <span class="font-bold text-slate-700">{{ $fixture->match_date->format('H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status</span>
                            <span class="font-bold {{ in_array($fixture->status, ['FT','AET','PEN']) ? 'text-green-600' : (in_array($fixture->status, ['LIVE','1H','HT','2H']) ? 'text-red-600' : 'text-blue-600') }}">
                                {{ $fixture->status }}
                            </span>
                        </div>
                        @if($fixture->venue_name)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Venue</span>
                            <span class="font-bold text-slate-700 text-right max-w-[200px]">{{ $fixture->venue_name }}{{ $fixture->venue_city ? ', ' . $fixture->venue_city : '' }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-slate-400">League</span>
                            <span class="font-bold text-slate-700">{{ $fixture->league_name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
