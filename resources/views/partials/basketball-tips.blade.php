<!-- Basketball Tips Section -->
@if(isset($basketballTips) && $basketballTips->count() > 0)
<section class="bg-gradient-to-br from-orange-50 to-red-50 py-16" id="basketball-tips">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-orange-100 border border-orange-200 text-orange-700 text-sm font-bold mb-4">
                <i class="fas fa-basketball-ball mr-2"></i> Basketball
            </div>
            <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mb-4">🏀 Basketball Predictions</h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                Expert NBA & top basketball league predictions with money line, spread, and total points analysis.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($basketballTips as $tip)
            <div class="bg-white rounded-2xl shadow-lg border border-orange-200 overflow-hidden hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 px-5 py-3 flex items-center justify-between">
                    <span class="text-white text-xs font-bold bg-white/20 px-2 py-0.5 rounded-full">{{ $tip->league_name }}</span>
                    <span class="text-orange-100 text-xs font-medium">{{ $tip->match_date->format('H:i') }}</span>
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-center flex-1">
                            <img src="{{ $tip->home_team_logo }}" class="w-12 h-12 object-contain mx-auto mb-2" alt="{{ $tip->home_team }}">
                            <span class="font-bold text-slate-800 text-sm">{{ $tip->home_team }}</span>
                        </div>
                        <span class="text-lg font-black text-slate-300 mx-4">VS</span>
                        <div class="text-center flex-1">
                            <img src="{{ $tip->away_team_logo }}" class="w-12 h-12 object-contain mx-auto mb-2" alt="{{ $tip->away_team }}">
                            <span class="font-bold text-slate-800 text-sm">{{ $tip->away_team }}</span>
                        </div>
                    </div>

                    @if($tip->predictions->isNotEmpty())
                        @php $pred = $tip->predictions->first(); @endphp
                        <div class="bg-orange-50 rounded-xl p-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Prediction</span>
                                <span class="font-bold text-orange-700">{{ $pred->tip }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Confidence</span>
                                <span class="font-bold text-green-600">{{ $pred->confidence }}%</span>
                            </div>
                            <p class="text-sm text-slate-600 mt-2">{{ Str::limit($pred->analysis, 150) }}</p>
                        </div>
                    @else
                        <div class="bg-slate-50 rounded-xl p-4 text-center text-slate-500">
                            Prediction coming soon...
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
