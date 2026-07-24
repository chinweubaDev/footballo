<!-- Sure Picks Section - 4 highest confidence picks -->
@if(isset($surePicksTips) && $surePicksTips->count() > 0)
<section class="bg-gradient-to-br from-green-50 to-emerald-50 py-16" id="sure-picks">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-green-100 border border-green-200 text-green-700 text-sm font-bold mb-4">
                <i class="fas fa-check-circle mr-2"></i> High Confidence
            </div>
            <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mb-4">Sure Picks Today</h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                4 most confident predictions backed by our advanced analytics engine. These are our best bets for today.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($surePicksTips as $tip)
            <div class="bg-white rounded-2xl shadow-lg border border-green-200 overflow-hidden hover:shadow-xl transition-all duration-300" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-white text-xs font-bold bg-white/20 px-2 py-0.5 rounded-full">{{ $tip->league_name }}</span>
                    </div>
                    <span class="text-green-100 text-xs font-medium">{{ $tip->match_date->format('H:i') }}</span>
                </div>
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <img src="{{ $tip->home_team_logo }}" class="w-10 h-10 object-contain" alt="{{ $tip->home_team }}">
                            <span class="font-bold text-slate-800">{{ $tip->home_team }}</span>
                        </div>
                        <div class="text-center px-4">
                            @if(in_array($tip->status, ['FT', 'AET', 'PEN']))
                                @php $tipPrediction = $tip->predictions->first(); $scoreWon = $tipPrediction && $tipPrediction->status === 'won'; @endphp
                                <span class="text-xl font-black px-3 py-1 rounded-lg {{ $scoreWon ? 'bg-green-600 text-white' : 'bg-slate-900 text-white' }}">{{ $tip->home_goals }} - {{ $tip->away_goals }}</span>
                            @else
                                <span class="text-lg font-bold text-slate-300">--</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="font-bold text-slate-800">{{ $tip->away_team }}</span>
                            <img src="{{ $tip->away_team_logo }}" class="w-10 h-10 object-contain" alt="{{ $tip->away_team }}">
                        </div>
                    </div>

                    @php $pd = $tip->prediction_data ?? null; @endphp
                    @if($pd)
                    <div class="bg-green-50 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Best Pick (1X2)</span>
                            <span class="font-bold text-green-700">{{ $pd['1x2']['label'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Over 2.5 Goals</span>
                            <span class="font-bold {{ $pd['over25']['pick'] === 'Over' ? 'text-green-600' : 'text-red-500' }}">
                                {{ $pd['over25']['pick'] }} ({{ $pd['over25']['confidence'] }}%)
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Both Teams to Score</span>
                            <span class="font-bold {{ $pd['bts']['pick'] === 'Yes' ? 'text-green-600' : 'text-red-500' }}">
                                {{ $pd['bts']['pick'] }} ({{ $pd['bts']['confidence'] }}%)
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Double Chance</span>
                            <span class="font-bold text-blue-600">{{ $pd['double_chance']['pick'] }} ({{ $pd['double_chance']['confidence'] }}%)</span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-green-200">
                            <span class="text-sm text-slate-600">Predicted Score</span>
                            <span class="font-bold text-slate-900">{{ $pd['correct_score']['most_likely'] }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm text-slate-500">Confidence</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 h-3 bg-green-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-green-500 to-green-600 rounded-full transition-all duration-500"
                                         style="width: {{ $pd['1x2']['confidence'] }}%"></div>
                                </div>
                                <span class="text-sm font-bold text-green-700">{{ $pd['1x2']['confidence'] }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-slate-600 leading-relaxed">
                        <p><strong>💡 Analysis:</strong> {{ Str::limit($pd['analysis'], 200) }}</p>
                    </div>
                    @else
                    <div class="bg-slate-50 rounded-xl p-4 text-center text-slate-500">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Loading prediction...
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
