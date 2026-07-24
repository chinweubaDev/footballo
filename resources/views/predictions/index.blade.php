@extends('layouts.app')

@section('title', 'Best Betting Tip & Accurate Football Prediction')
@section('meta_description', 'Get the best betting tip and accurate football prediction for today. Our expert analysts provide winning tips for today across all markets.')
@section('meta_keywords', 'bet tips, best betting tip, accurate football prediction, winning tips for today, sure tips prediction')

@section('content')
<div class="bg-slate-50 min-h-screen pb-20">
    <!-- Hero/Header Section -->
    <section class="relative bg-slate-900 pt-32 pb-20 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #22c55e 1px, transparent 0); background-size: 40px 40px;"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                <div data-aos="fade-right">
                    <h1 class="text-4xl lg:text-5xl font-black text-white mb-4">Accurate Football Prediction for Today</h1>
                    <p class="text-slate-400 text-lg max-w-xl">Browse our complete database of daily football predictions across major leagues and markets.</p>
                </div>
                
                <!-- Filters -->
                <form method="GET" action="{{ route('predictions') }}" class="flex flex-wrap gap-4" data-aos="fade-left">
                    <div class="relative min-w-[200px]">
                        <select name="league" onchange="this.form.submit()" class="w-full bg-slate-800 border-none text-white text-sm font-bold rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary-500 appearance-none transition-all cursor-pointer">
                            <option value="">All Leagues</option>
                            @foreach($leagues as $league)
                                <option value="{{ $league }}" {{ request('league') === $league ? 'selected' : '' }}>{{ $league }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    <div class="relative min-w-[200px]">
                        <select name="category" onchange="this.form.submit()" class="w-full bg-slate-800 border-none text-white text-sm font-bold rounded-2xl px-6 py-4 focus:ring-2 focus:ring-primary-500 appearance-none transition-all cursor-pointer">
                            <option value="">All Categories</option>
                            <option value="1X2" {{ request('category') === '1X2' ? 'selected' : '' }}>1X2</option>
                            <option value="Over/Under" {{ request('category') === 'Over/Under' ? 'selected' : '' }}>Over/Under</option>
                            <option value="BTS" {{ request('category') === 'BTS' ? 'selected' : '' }}>Both Teams to Score</option>
                            <option value="Double Chance" {{ request('category') === 'Double Chance' ? 'selected' : '' }}>Double Chance</option>
                            <option value="Draw" {{ request('category') === 'Draw' ? 'selected' : '' }}>Draw</option>
                        </select>
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500">
                            <i class="fas fa-filter text-xs"></i>
                        </div>
                    </div>
                    @if(request('league') || request('category'))
                        <a href="{{ route('predictions') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 text-white text-xs font-bold rounded-xl hover:bg-slate-600 transition-colors">
                            <i class="fas fa-times mr-1.5"></i> Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </section>

    <!-- Predictions Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        @if($fixturesByLeague->count() > 0)
            @foreach($fixturesByLeague as $leagueName => $fixtures)
            <div class="mb-12" data-aos="fade-up">
                <div class="flex items-center space-x-4 mb-6 sticky top-24 z-20 bg-slate-50/80 backdrop-blur-md py-4 rounded-2xl px-4">
                    <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center">
                        @if($fixtures[0]->league_logo)
                            <img src="{{ $fixtures[0]->league_logo }}" alt="{{ $leagueName }}" class="w-8 h-8 object-contain">
                        @else
                            <i class="fas fa-trophy text-primary-500"></i>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                             <span class="text-[10px] font-black text-primary-600 uppercase tracking-widest">{{ $fixtures[0]->league_country ?? 'International' }}</span>
                             <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                             <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $fixtures->count() }} Matches</span>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900">{{ $leagueName }}</h2>
                    </div>
                </div>
                
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left responsive-table">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Time</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Match</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Score</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tip</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Conf</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right pr-6">Odds</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($fixtures as $fixture)
                                @foreach($fixture->predictions as $prediction)
                                <tr class="hover:bg-slate-50 transition-colors cursor-pointer" onclick="window.location='{{ route('match.detail', $fixture->id) }}'">
                                    <td class="px-6 py-4" data-label="Time">
                                        <span class="font-bold text-slate-900">{{ $fixture->match_date->format('H:i') }}</span>
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">{{ $prediction->category }}</span>
                                    </td>
                                    <td class="px-6 py-4" data-label="Match">
                                        <div class="flex items-center gap-2">
                                            @if($fixture->home_team_logo)
                                                <img src="{{ $fixture->home_team_logo }}" class="w-5 h-5 object-contain">
                                            @endif
                                            <span class="text-sm font-bold text-slate-800">{{ $fixture->home_team }}</span>
                                            <span class="text-[10px] text-slate-300">vs</span>
                                            <span class="text-sm font-bold text-slate-800">{{ $fixture->away_team }}</span>
                                            @if($fixture->away_team_logo)
                                                <img src="{{ $fixture->away_team_logo }}" class="w-5 h-5 object-contain">
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center" data-label="Score">
                                        @if(in_array($fixture->status, ['FT','AET','PEN']))
                                            @php $scoreWon = $prediction->status === 'won'; @endphp
                                            <span class="font-black px-3 py-1.5 rounded-lg {{ $scoreWon ? 'bg-green-600 text-white' : 'bg-slate-900 text-white' }}">{{ $fixture->home_goals }} – {{ $fixture->away_goals }}</span>
                                        @elseif(in_array($fixture->status, ['LIVE','1H','2H','HT','ET','BT']))
                                            <span class="font-black bg-red-100 text-red-700 px-2 py-1 rounded-lg">{{ $fixture->home_goals ?? 0 }} – {{ $fixture->away_goals ?? 0 }}</span>
                                        @else
                                            <span class="text-slate-300 font-bold">––</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4" data-label="Tip">
                                        <span class="px-3 py-1.5 bg-primary-50 text-primary-700 rounded-xl text-sm font-bold">{{ $prediction->tip }}</span>
                                    </td>
                                    <td class="px-6 py-4" data-label="Conf">
                                        <span class="font-bold text-primary-600">{{ $prediction->confidence }}%</span>
                                    </td>
                                    <td class="px-6 py-4 text-right pr-6" data-label="Odds">
                                        <span class="font-black text-slate-900">{{ $prediction->odds ?? '-' }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-[3rem] p-20 text-center shadow-xl border border-slate-200">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-futbol text-slate-300 text-4xl animate-bounce"></i>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-4">No Predictions Found</h2>
                <p class="text-slate-500 mb-8 max-w-md mx-auto">We're currently analyzing today's fixtures. Check back shortly for new professional tips.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white bg-primary-600 rounded-2xl hover:bg-primary-700 transition-all">
                    Back to Homepage
                </a>
            </div>
        @endif

        <!-- Pagination -->
        @if(isset($fixtures) && method_exists($fixtures, 'hasPages') && $fixtures->hasPages())
        <div class="mt-12">
            {{ $fixtures->links() }}
        </div>
        @endif

        <!-- CTA Section -->
        <div class="relative bg-slate-900 rounded-[3rem] overflow-hidden p-12 mt-20 group">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-600/20 to-blue-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            <div class="relative text-center max-w-2xl mx-auto">
                <h2 class="text-3xl lg:text-4xl font-black text-white mb-6">Want 95%+ Accuracy?</h2>
                <p class="text-slate-400 mb-10 text-lg">Unlock our exclusive VIP algorithms and get access to the highest margin predictions in the market.</p>
                <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-white bg-primary-600 rounded-2xl hover:bg-primary-700 shadow-xl shadow-primary-600/20 transition-all hover:-translate-y-1">
                    Upgrade to VIP Access
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
