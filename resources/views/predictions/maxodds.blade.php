        <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg shadow-xl p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Maxodds Tips</h1>
                    <p class="text-purple-100">High-value predictions with maximum odds potential</p>
                </div>
                <div class="text-right">
                    <i class="fas fa-chart-line text-4xl text-purple-300"></i>
                </div>
            </div>
        </div>

        <!-- Maxodds Info -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-8">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-purple-600 text-2xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-semibold text-purple-800">What are Maxodds Tips?</h3>
                    <p class="text-purple-700">Maxodds tips are carefully selected predictions that offer the highest potential returns. These are high-risk, high-reward predictions with odds typically above 2.0.</p>
                </div>
            </div>
        </div>

        <!-- Maxodds Predictions -->
        @if($fixturesByLeague->count() > 0)
            @foreach($fixturesByLeague as $leagueName => $fixtures)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <div class="flex items-center mb-6">
                    <i class="fas fa-chart-line text-purple-500 text-2xl mr-3"></i>
                    <h2 class="text-xl font-bold text-gray-800">{{ $leagueName }}</h2>
                    <span class="ml-2 bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        Maxodds
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($fixtures as $fixture)
                        @foreach($fixture->predictions->where('is_maxodds', true) as $prediction)
                        <div class="border border-purple-200 rounded-lg p-6 hover:shadow-md transition duration-150 ease-in-out bg-gradient-to-br from-purple-50 to-white">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 text-sm">{{ $fixture->home_team }} vs {{ $fixture->away_team }}</h4>
                                    <p class="text-xs text-gray-500">{{ $fixture->match_date->format('M d, H:i') }}</p>
                                </div>
                                <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded-full">
                                    <i class="fas fa-chart-line mr-1"></i>Maxodds
                                </span>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Category:</span>
                                    <span class="font-semibold text-blue-600">{{ $prediction->category }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Prediction:</span>
                                    <span class="font-semibold text-green-600">{{ $prediction->tip }}</span>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Confidence:</span>
                                    <div class="flex items-center">
                                        <div class="w-12 bg-gray-200 rounded-full h-1.5 mr-2">
                                            <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ $prediction->confidence }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium">{{ $prediction->confidence }}%</span>
                                    </div>
                                </div>
                                
                                @if($prediction->odds)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Odds:</span>
                                    <span class="font-semibold text-purple-600 text-lg">{{ $prediction->odds }}</span>
                                </div>
                                @endif
                                
                                @if($prediction->analysis)
                                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-600 italic">{{ $prediction->analysis }}</p>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Risk Warning -->
                            <div class="mt-4 pt-4 border-t border-purple-200">
                                <div class="flex items-center text-xs text-purple-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <span>High risk, high reward prediction</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
            @endforeach
        @else
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <i class="fas fa-chart-line text-purple-300 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No Maxodds Tips Available</h2>
            <p class="text-gray-600 mb-6">Maxodds tips will appear here when our experts identify high-value opportunities.</p>
            <a href="{{ route('predictions') }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                View All Predictions
            </a>
        </div>
        @endif

        <!-- Maxodds Strategy -->
        <div class="bg-white rounded-lg shadow-lg p-8 mt-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Maxodds Strategy</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">What Makes a Good Maxodds Tip?</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span>Odds typically above 2.0 (100% return)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span>Strong statistical backing</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span>Value identified in the market</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 mr-2 mt-1"></i>
                            <span>Confidence level above 60%</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk Management</h3>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-1"></i>
                            <span>Only bet what you can afford to lose</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-1"></i>
                            <span>Diversify your betting portfolio</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-1"></i>
                            <span>Set strict bankroll limits</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-1"></i>
                            <span>Never chase losses</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Success Stories -->
        <div class="bg-gradient-to-r from-green-600 to-green-800 rounded-lg shadow-xl p-8 mt-8 text-white">
            <h2 class="text-2xl font-bold text-center mb-8">Success Stories</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trophy text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">John D.</h3>
                    <p class="text-green-100">"Maxodds tips helped me turn $100 into $2,500 in just one month!"</p>
                </div>
                <div class="text-center">
                    <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Sarah M.</h3>
                    <p class="text-green-100">"The high-value predictions have been game-changers for my betting strategy."</p>
                </div>
                <div class="text-center">
                    <div class="bg-white bg-opacity-20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-star text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Mike R.</h3>
                    <p class="text-green-100">"Best investment I've made. The returns speak for themselves."</p>
                </div>
            </div>
        </div>

        <!-- Disclaimer -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mt-8">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-4 mt-1"></i>
                <div>
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">Important Disclaimer</h3>
                    <p class="text-yellow-700 text-sm">
                        Maxodds tips are high-risk predictions with potential for high returns. Past performance does not guarantee future results. 
                        Always bet responsibly and within your means. Gambling can be addictive - please seek help if you feel you have a problem.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
