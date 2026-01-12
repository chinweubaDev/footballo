@extends('layouts.app')

@section('title', 'Maxodds High-Value Predictions')

@section('content')
<div class="bg-slate-900 min-h-screen pb-20 overflow-hidden">
    <!-- Maxodds Hero -->
    <section class="relative pt-32 pb-40">
        <!-- Background elements -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-indigo-600/10 blur-[150px] rounded-full"></div>
            <div class="absolute top-40 right-0 w-[400px] h-[400px] bg-purple-600/10 blur-[100px] rounded-full translate-x-1/2"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-aos="zoom-in">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 text-xs font-black uppercase tracking-[0.2em] mb-8">
                <i class="fas fa-rocket mr-2"></i> High Risk • High Reward
            </div>
            <h1 class="text-5xl lg:text-7xl font-black text-white mb-8 tracking-tighter">
                Maxodds <br>
                <span class="bg-gradient-to-r from-purple-400 via-indigo-500 to-purple-600 bg-clip-text text-transparent">Mega Value</span>
            </h1>
            <p class="text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed mb-12">
                Our Maxodds strategy identifies significant market mispricings and high-margin opportunities that conventional models often overlook.
            </p>
            
            <div class="flex flex-wrap justify-center gap-6">
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/5 rounded-3xl p-6 text-center min-w-[160px]">
                    <div class="text-3xl font-black text-white mb-1">5.0+</div>
                    <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Average Odds</div>
                </div>
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/5 rounded-3xl p-6 text-center min-w-[160px]">
                    <div class="text-3xl font-black text-white mb-1">Elite</div>
                    <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Strategy</div>
                </div>
                <div class="bg-slate-800/40 backdrop-blur-md border border-white/5 rounded-3xl p-6 text-center min-w-[160px]">
                    <div class="text-3xl font-black text-white mb-1">Daily</div>
                    <div class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Updates</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10">
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Sidebar: Why Maxodds? -->
            <div class="lg:col-span-1 space-y-8" data-aos="fade-right">
                <div class="bg-slate-800/50 backdrop-blur-xl border border-white/10 rounded-[2.5rem] p-10 ring-1 ring-white/5">
                    <h3 class="text-2xl font-black text-white mb-8">The Maxodds Strategy</h3>
                    <div class="space-y-8">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-purple-500/10 rounded-2xl flex items-center justify-center text-purple-400 shrink-0">
                                <i class="fas fa-search-dollar"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-2">Market Mispricing</h4>
                                <p class="text-sm text-slate-400 leading-relaxed">We find matches where bookmakers have significantly over-valued the underdog.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-indigo-500/10 rounded-2xl flex items-center justify-center text-indigo-400 shrink-0">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-2">Proprietary AI</h4>
                                <p class="text-sm text-slate-400 leading-relaxed">Cross-referencing 10+ years of historical data to detect recurring high-odds patterns.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-pink-500/10 rounded-2xl flex items-center justify-center text-pink-400 shrink-0">
                                <i class="fas fa-biohazard"></i>
                            </div>
                            <div>
                                <h4 class="text-white font-bold mb-2">Calculated Risk</h4>
                                <p class="text-sm text-slate-400 leading-relaxed">While risk is higher, the mathematical value ensures long-term profitability.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-12 pt-10 border-t border-white/5">
                        <div class="p-6 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-3xl text-white text-center shadow-2xl shadow-purple-600/20">
                            <h5 class="text-lg font-black mb-2">VIP Maxodds</h5>
                            <p class="text-xs text-purple-100 mb-6 font-medium">Get 2x more high-odds tips daily with our VIP plan.</p>
                            <a href="{{ route('pricing') }}" class="block w-full py-3 bg-white text-indigo-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition-colors">Upgrade Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content: Match List -->
            <div class="lg:col-span-2 space-y-8">
                @forelse($fixtures as $fixture)
                    @foreach($fixture->predictions as $prediction)
                    <div class="bg-white rounded-[3rem] p-1 shadow-2xl transition-all duration-500 group" data-aos="fade-up">
                        <div class="bg-slate-50 rounded-[2.8rem] p-8 md:p-10 h-full border border-white">
                            <!-- Match Meta Header -->
                            <div class="flex items-center justify-between mb-10 pb-6 border-b border-slate-200/50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-white rounded-2xl shadow-sm flex items-center justify-center border border-slate-100 p-2">
                                        <img src="{{ $fixture->league_logo }}" class="w-full h-full object-contain opacity-70">
                                    </div>
                                    <div>
                                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">{{ $fixture->league_country }}</div>
                                        <div class="text-sm font-black text-slate-900 leading-none">{{ $fixture->league_name }}</div>
                                    </div>
                                </div>
                                <span class="bg-purple-100 text-purple-700 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full ring-1 ring-purple-200">
                                    MAXODDS PICK
                                </span>
                            </div>

                            <!-- Match Center -->
                            <div class="flex flex-col md:flex-row items-center justify-between gap-10 mb-12">
                                <div class="flex-1 flex flex-col items-center text-center">
                                    <div class="w-20 h-20 rounded-[2rem] bg-white shadow-xl shadow-slate-200/50 flex items-center justify-center p-4 mb-4 transform transition-transform group-hover:scale-110">
                                        <img src="{{ $fixture->home_team_logo }}" class="w-full h-full object-contain">
                                    </div>
                                    <h4 class="text-lg font-black text-slate-900">{{ $fixture->home_team }}</h4>
                                    <span class="text-[10px] font-bold text-slate-300 uppercase mt-1 tracking-widest">Home Team</span>
                                </div>
                                
                                <div class="flex flex-col items-center shrink-0">
                                    <div class="text-xs font-black text-slate-300 mb-2 uppercase tracking-[0.3em]">VS</div>
                                    <div class="h-12 w-px bg-slate-200/50"></div>
                                    <div class="text-[9px] font-black text-slate-400 mt-2 uppercase tracking-widest">{{ $fixture->match_date->format('H:i') }}</div>
                                </div>

                                <div class="flex-1 flex flex-col items-center text-center">
                                    <div class="w-20 h-20 rounded-[2rem] bg-white shadow-xl shadow-slate-200/50 flex items-center justify-center p-4 mb-4 transform transition-transform group-hover:scale-110">
                                        <img src="{{ $fixture->away_team_logo }}" class="w-full h-full object-contain">
                                    </div>
                                    <h4 class="text-lg font-black text-slate-900">{{ $fixture->away_team }}</h4>
                                    <span class="text-[10px] font-bold text-slate-300 uppercase mt-1 tracking-widest">Away Team</span>
                                </div>
                            </div>

                            <!-- Prediction Footer Wrap -->
                            <div class="grid md:grid-cols-2 gap-6 items-end">
                                <div class="bg-white rounded-3xl p-6 ring-1 ring-slate-100 shadow-sm border border-slate-50">
                                    <div class="flex justify-between items-center mb-6">
                                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Value Market</span>
                                        <span class="text-xs font-black text-purple-600 uppercase tracking-widest">{{ $prediction->category }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest block mb-0.5">Tip Output</span>
                                            <span class="text-xl font-black text-slate-900 italic">"{{ $prediction->tip }}"</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest block mb-0.5">Best Odds</span>
                                            <span class="text-3xl font-black text-slate-900">{{ $prediction->odds ?? '4.50' }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between mb-1">
                                         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reward Potential</span>
                                         <span class="text-xs font-black text-slate-900">HIGH</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2 p-0.5">
                                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 h-1 rounded-full shadow-lg shadow-purple-500/20" style="width: 85%"></div>
                                    </div>
                                    <a href="{{ route('predictions') }}" class="flex items-center justify-between w-full p-4 bg-slate-900 rounded-2xl text-white hover:bg-purple-600 transition-all group/btn shadow-xl shadow-slate-900/10 hover:shadow-purple-600/20">
                                        <span class="text-[10px] font-black uppercase tracking-widest">Full Analysis</span>
                                        <i class="fas fa-arrow-right text-xs group-hover/btn:translate-x-1 transition-transform"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @empty
                    <div class="bg-slate-800/30 backdrop-blur-xl rounded-[4rem] p-24 text-center border-2 border-dashed border-white/10" data-aos="zoom-in">
                        <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-10 border border-white/10">
                            <i class="fas fa-satellite-dish text-white/20 text-4xl animate-pulse"></i>
                        </div>
                        <h3 class="text-3xl font-black text-white mb-4">No Maxodds Detected</h3>
                        <p class="text-slate-500 text-lg mb-12 max-w-md mx-auto">Our algorithms are currently monitoring live market movements for high-value mispricings. Check back in a few hours.</p>
                        <a href="{{ route('home') }}" class="px-10 py-5 bg-white text-slate-950 font-black rounded-2xl hover:bg-slate-200 transition-all">
                            Back to Dashboard
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Strategy Disclaimer -->
    <section class="mt-32 border-t border-white/5 pt-20 pb-20 bg-slate-950/50">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h5 class="text-white font-black text-base uppercase tracking-widest mb-6 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-purple-500 mr-3"></i> Responsible Growth
            </h5>
            <p class="text-sm text-slate-500 leading-relaxed italic">
                 Maxodds tips are high-variance selections. While the mathematical value is high, win frequency is lower than our standard VIP picks. We recommend a strict bankroll management strategy (1-2% per stake) for this specific market.
            </p>
        </div>
    </section>
</div>

<style>
    /* Custom hover effect for match cards */
    .group:hover .group-team-logo {
        transform: scale(1.1) translateY(-5px);
    }
</style>
@endsection
