            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Premium Tips</h1>
                    <p class="text-yellow-100">Exclusive predictions with higher accuracy rates</p>
                </div>
                <div class="text-right">
                    <i class="fas fa-crown text-4xl text-yellow-300"></i>
                </div>
            </div>
        </div>

        <!-- Premium Access Check -->
        @auth
            @if(!auth()->user()->hasActivePremium())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                <div class="flex items-center">
                    <i class="fas fa-lock text-yellow-600 text-2xl mr-4"></i>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-yellow-800">Premium Access Required</h3>
                        <p class="text-yellow-700">You need an active premium subscription to view these exclusive predictions.</p>
                    </div>
                    <a href="{{ route('pricing') }}" class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700 transition duration-150 ease-in-out">
                        Upgrade Now
                    </a>
                </div>
            </div>
            @endif
        @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <div class="flex items-center">
                <i class="fas fa-user-lock text-blue-600 text-2xl mr-4"></i>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-blue-800">Login Required</h3>
                    <p class="text-blue-700">Please login and subscribe to premium to access these exclusive predictions.</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('login') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                        Register
                    </a>
                </div>
            </div>
        </div>
        @endauth

        <!-- Premium Predictions -->
        @if((auth()->check() && auth()->user()->hasActivePremium()) || !auth()->check())
            @if($fixturesByLeague->count() > 0)
                @foreach($fixturesByLeague as $leagueName => $fixtures)
                <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <div class="flex items-center mb-6">
                        <i class="fas fa-crown text-yellow-500 text-2xl mr-3"></i>
                        <h2 class="text-xl font-bold text-gray-800">{{ $leagueName }}</h2>
                        <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            Premium
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($fixtures as $fixture)
                            @foreach($fixture->predictions->where('is_premium', true) as $prediction)
                            <div class="border border-yellow-200 rounded-lg p-6 hover:shadow-md transition duration-150 ease-in-out bg-gradient-to-br from-yellow-50 to-white">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800 text-sm">{{ $fixture->home_team }} vs {{ $fixture->away_team }}</h4>
                                        <p class="text-xs text-gray-500">{{ $fixture->match_date->format('M d, H:i') }}</p>
                                    </div>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">
                                        <i class="fas fa-crown mr-1"></i>Premium
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
                                        <span class="font-semibold">{{ $prediction->odds }}</span>
                                    </div>
                                    @endif
                                    
                                    @if($prediction->analysis)
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                        <p class="text-xs text-gray-600 italic">{{ $prediction->analysis }}</p>
                                    </div>
                                    @endif
                                </div>
                                
                                @if(!auth()->check() || !auth()->user()->hasActivePremium())
                                <div class="mt-4 pt-4 border-t border-yellow-200">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-yellow-600">
                                            <i class="fas fa-lock mr-1"></i>Premium Content
                                        </span>
                                        <a href="{{ route('pricing') }}" class="text-xs bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 transition duration-150 ease-in-out">
                                            Subscribe
                                        </a>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                @endforeach
            @else
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <i class="fas fa-crown text-yellow-300 text-6xl mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">No Premium Predictions Available</h2>
                <p class="text-gray-600 mb-6">Premium predictions will appear here once they are added by our experts.</p>
                <a href="{{ route('predictions') }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                    View All Predictions
                </a>
            </div>
            @endif
        @endif

        <!-- Premium Benefits -->
        <div class="bg-white rounded-lg shadow-lg p-8 mt-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Why Choose Premium?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-yellow-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Higher Accuracy</h3>
                    <p class="text-gray-600">Premium predictions have a proven track record with 85%+ accuracy rates.</p>
                </div>
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Expert Analysis</h3>
                    <p class="text-gray-600">Get detailed analysis from our team of professional football analysts.</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Early Access</h3>
                    <p class="text-gray-600">Get predictions before they go public and secure the best odds.</p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        @if(!auth()->check() || !auth()->user()->hasActivePremium())
        <div class="bg-gradient-to-r from-yellow-600 to-yellow-800 rounded-lg shadow-xl p-8 mt-8 text-white text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to Win More?</h2>
            <p class="text-xl mb-6">Join thousands of successful bettors who trust our premium predictions.</p>
            <a href="{{ route('pricing') }}" class="bg-white text-yellow-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-150 ease-in-out">
                Get Premium Access
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
