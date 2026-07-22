@extends('layouts.app')

@section('title', 'Best Betting Tip & Winning Tips for Today - VIP Premium')
@section('meta_description', 'Get the best betting tip and winning tips for today with our VIP premium selections. 95%+ accuracy for sure tips prediction.')
@section('meta_keywords', 'best betting tip, winning tips for today, sure tips prediction, accurate football prediction')

@section('content')
<div class="bg-slate-950 min-h-screen pb-20">
    <!-- Premium Hero -->
    <section class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 pt-32 pb-32 overflow-hidden">
        <!-- Golden background effects -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #eab308 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="absolute -top-24 -right-24 w-[500px] h-[500px] bg-yellow-500/10 blur-[120px] rounded-full animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-primary-500/5 blur-[100px] rounded-full translate-y-1/2"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-16">
                <div class="lg:w-1/2 text-center lg:text-left" data-aos="fade-right">
                    <div class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-xs font-black uppercase tracking-[0.2em] mb-8">
                        <i class="fas fa-crown mr-2 mb-0.5"></i> Elite VIP Access
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-black text-white mb-6 leading-tight">
                        Best Betting <br>
                        <span class="bg-gradient-to-r from-yellow-300 via-yellow-500 to-yellow-600 bg-clip-text text-transparent italic">Winning Tips</span>
                    </h1>
                    <p class="text-xl text-slate-400 mb-10 leading-relaxed max-w-xl mx-auto lg:mx-0">
                        Gain access to our most accurate, hand-picked predictions. Our VIP algorithm focuses on low-risk, high-probability markets for consistent daily growth.
                    </p>
                    <div class="flex items-center justify-center lg:justify-start space-x-8">
                        <div class="text-center">
                            <div class="text-3xl font-black text-white">95%</div>
                            <div class="text-[10px] text-slate-500 font-black uppercase tracking-widest mt-1">Accuracy</div>
                        </div>
                        <span class="w-px h-10 bg-slate-800"></span>
                        <div class="text-center">
                            <div class="text-3xl font-black text-white">Daily</div>
                            <div class="text-[10px] text-slate-500 font-black uppercase tracking-widest mt-1">Updates</div>
                        </div>
                        <span class="w-px h-10 bg-slate-800"></span>
                        <div class="text-center">
                            <div class="text-3xl font-black text-white">Expert</div>
                            <div class="text-[10px] text-slate-500 font-black uppercase tracking-widest mt-1">Analysis</div>
                        </div>
                    </div>
                </div>
                
                <div class="lg:w-1/2 hidden lg:block" data-aos="fade-left">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gradient-to-tr from-yellow-500/50 to-primary-500/50 blur-2xl rounded-[3rem] opacity-30"></div>
                        <div class="relative bg-slate-900/50 backdrop-blur-3xl border border-white/5 rounded-[2.5rem] p-10 shadow-2xl overflow-hidden ring-1 ring-white/10">
                            <div class="flex items-center justify-between mb-8 border-b border-white/5 pb-6">
                                <h3 class="text-white font-black text-lg">VIP Stats Today</h3>
                                <span class="text-yellow-500 font-bold text-xs uppercase tracking-widest animate-pulse">Live Tracking</span>
                            </div>
                            <div class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 text-sm">Win Rate</span>
                                    <span class="text-white font-black">94.8%</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-yellow-500 h-full w-[94.8%]"></div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 text-sm">Average Odds</span>
                                    <span class="text-white font-black">1.85 - 2.50</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 text-sm">Success Streak</span>
                                    <span class="text-green-400 font-black flex items-center">
                                        <i class="fas fa-fire mr-2"></i> 7 Days
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 relative z-10">
        @if(auth()->user() && auth()->user()->hasActiveSubscription())
            <!-- VIP User Content -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($fixtures as $fixture)
                    @foreach($fixture->predictions as $prediction)
                    <div class="bg-slate-900 rounded-[3rem] p-8 border border-white/5 hover:border-yellow-500/30 transition-all duration-500 group relative overflow-hidden shadow-2xl" data-aos="fade-up">
                        <div class="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <i class="fas fa-crown text-8xl text-yellow-500 -rotate-12"></i>
                        </div>
                        
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-10 pb-6 border-b border-white/5">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span>
                                <span class="text-[10px] font-black text-yellow-500 uppercase tracking-[0.2em] italic">VIP SELECTION</span>
                            </div>
                            <span class="text-xs font-bold text-slate-500">{{ $fixture->match_date->format('M d, H:i') }}</span>
                        </div>

                        <!-- Match with score -->
                        <div class="space-y-4 mb-10 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <span class="text-base font-black text-white group-hover/team:text-yellow-500 transition-colors">{{ $fixture->home_team }}</span>
                                <span class="text-xl text-slate-400">vs</span>
                                <span class="text-base font-black text-white group-hover/team:text-yellow-500 transition-colors">{{ $fixture->away_team }}</span>
                            </div>
                            <div class="mt-3">
                                @if(in_array($fixture->status, ['FT', 'AET', 'PEN']))
                                    <span class="text-3xl font-black text-yellow-400 bg-white/10 px-4 py-1.5 rounded-xl">{{ $fixture->home_goals }} – {{ $fixture->away_goals }}</span>
                                    <span class="block text-[10px] text-green-400 font-bold mt-1">FULL TIME</span>
                                @elseif(in_array($fixture->status, ['LIVE','1H','HT','2H']))
                                    <span class="text-3xl font-black text-red-400 bg-white/10 px-4 py-1.5 rounded-xl animate-pulse">{{ $fixture->home_goals ?? 0 }} – {{ $fixture->away_goals ?? 0 }}</span>
                                    <span class="block text-[10px] text-red-400 font-bold mt-1">LIVE</span>
                                @else
                                    <span class="text-2xl font-black text-slate-500">––</span>
                                @endif
                            </div>
                            </div>
                        </div>

                        <!-- Prediction Card -->
                        <div class="bg-gradient-to-br from-yellow-500/10 to-yellow-600/5 rounded-[2rem] p-6 border border-yellow-500/10 mb-8 transform transition-transform group-hover:scale-[1.03] duration-500">
                             <span class="text-[10px] font-black text-yellow-500 uppercase tracking-widest block mb-1.5 opacity-60">Verified Accuracy Tip</span>
                             <div class="flex justify-between items-end">
                                 <span class="text-2xl font-black text-white tracking-tight">{{ $prediction->tip }}</span>
                                 <div class="text-right">
                                     <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-0.5">Odds</span>
                                     <span class="text-3xl font-black text-yellow-500 leading-none">{{ $prediction->odds ?? '1.95' }}</span>
                                 </div>
                             </div>
                        </div>

                        <!-- Analysis -->
                        <div class="mb-8">
                            <div class="flex items-center space-x-2 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">
                                <i class="fas fa-microscope text-yellow-500"></i>
                                <span>Expert Analysis</span>
                            </div>
                            <p class="text-[12px] text-slate-400 leading-relaxed italic border-l-2 border-yellow-500/20 pl-4 py-1">
                                "{{ $prediction->analysis ?? 'Our models show significant value in this selection based on recent performance metrics and squad availability.' }}"
                            </p>
                        </div>

                        <button class="w-full py-4 rounded-2xl bg-slate-800 text-white font-black text-sm uppercase tracking-widest border border-white/5 hover:bg-yellow-500 hover:text-slate-950 transition-all duration-300">
                             Save to Slip <i class="fas fa-plus ml-2"></i>
                        </button>
                    </div>
                    @endforeach
                @endforeach
            </div>
        @else
            <!-- Locked State UI -->
            <div class="max-w-4xl mx-auto py-20 px-8 relative" data-aos="zoom-in">
                <!-- Blur effect backdrop -->
                <div class="absolute inset-0 bg-white/5 backdrop-blur-2xl rounded-[4rem] border border-white/10 overflow-hidden shadow-2xl">
                    <!-- Placeholder "blurred" cards in background -->
                    <div class="grid grid-cols-2 gap-8 p-12 opacity-10 pointer-events-none scale-105">
                        @for($i=0; $i<4; $i++)
                        <div class="bg-slate-800 h-64 rounded-[3rem] border border-white/10"></div>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10 text-center py-20 lg:py-32">
                    <div class="w-24 h-24 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-10 shadow-2xl shadow-yellow-500/40 transform -rotate-6">
                        <i class="fas fa-lock text-3xl text-slate-950"></i>
                    </div>
                    <h2 class="text-4xl lg:text-6xl font-black text-white mb-8 tracking-tight">Unlock Premium Tips</h2>
                    <p class="text-xl text-slate-400 mb-12 max-w-2xl mx-auto leading-relaxed">
                        This content is reserved for our VIP members. Upgrade your account today to access our highest confidence predictions and expert value analysis.
                    </p>
                    
                    <div class="grid md:grid-cols-3 gap-4 mb-14 max-w-3xl mx-auto">
                        <div class="bg-slate-900/50 p-6 rounded-3xl border border-white/5 backdrop-blur-md">
                            <i class="fas fa-check-circle text-yellow-500 mb-3 block text-xl"></i>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Accuracy</span>
                            <div class="text-white font-bold mt-1">95.2% Certified</div>
                        </div>
                        <div class="bg-slate-900/50 p-6 rounded-3xl border border-white/5 backdrop-blur-md">
                            <i class="fas fa-gem text-yellow-500 mb-3 block text-xl"></i>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Markets</span>
                            <div class="text-white font-bold mt-1">Exclusive Value</div>
                        </div>
                        <div class="bg-slate-900/50 p-6 rounded-3xl border border-white/5 backdrop-blur-md">
                            <i class="fas fa-headset text-yellow-500 mb-3 block text-xl"></i>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Support</span>
                            <div class="text-white font-bold mt-1">24/7 VIP Help</div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-6 justify-center">
                        <a href="{{ route('pricing') }}" class="px-12 py-5 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 text-slate-950 font-black text-xl rounded-2xl hover:shadow-2xl hover:shadow-yellow-500/30 transition-all hover:-translate-y-1">
                            Get Access Now <i class="fas fa-arrow-right ml-3"></i>
                        </a>
                        <a href="{{ route('login') }}" class="px-12 py-5 bg-white/5 border border-white/10 text-white font-black text-xl rounded-2xl hover:bg-white/10 transition-all">
                            Member Login
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Features Banner -->
    <section class="mt-32 border-t border-white/5 pt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center group" data-aos="fade-up" data-aos-delay="0">
                    <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/5 group-hover:border-yellow-500/30 transition-colors">
                        <i class="fas fa-shield-alt text-2xl text-yellow-500/60 group-hover:text-yellow-500"></i>
                    </div>
                    <h4 class="text-white font-black text-sm mb-2 uppercase tracking-widest">Verified Results</h4>
                    <p class="text-xs text-slate-500 leading-relaxed px-4">Every prediction is tracked and verified for complete transparency.</p>
                </div>
                <div class="text-center group" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/5 group-hover:border-yellow-500/30 transition-colors">
                        <i class="fas fa-bolt text-2xl text-yellow-500/60 group-hover:text-yellow-500"></i>
                    </div>
                    <h4 class="text-white font-black text-sm mb-2 uppercase tracking-widest">Instant Delivery</h4>
                    <p class="text-xs text-slate-500 leading-relaxed px-4">Get tips as soon as the line moves and value is detected.</p>
                </div>
                <div class="text-center group" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/5 group-hover:border-yellow-500/30 transition-colors">
                        <i class="fas fa-chart-pie text-2xl text-yellow-500/60 group-hover:text-yellow-500"></i>
                    </div>
                    <h4 class="text-white font-black text-sm mb-2 uppercase tracking-widest">Probability Math</h4>
                    <p class="text-xs text-slate-500 leading-relaxed px-4">Based on advanced Poisson distribution and Elo ratings.</p>
                </div>
                <div class="text-center group" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/5 group-hover:border-yellow-500/30 transition-colors">
                        <i class="fas fa-user-tie text-2xl text-yellow-500/60 group-hover:text-yellow-500"></i>
                    </div>
                    <h4 class="text-white font-black text-sm mb-2 uppercase tracking-widest">Analyst Review</h4>
                    <p class="text-xs text-slate-500 leading-relaxed px-4">Every algorithm output is cross-checked by human experts.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
