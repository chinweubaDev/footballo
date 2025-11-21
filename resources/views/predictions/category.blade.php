@extends('layouts.app')

@section('title', $category . ' Predictions')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                {{ $category }} Predictions
            </h1>
            <p class="text-xl text-gray-600">Expert predictions for {{ $category }} betting markets</p>
        </div>

        <!-- Category Navigation -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Browse by Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <a href="{{ route('predictions') }}" class="flex items-center justify-center p-3 bg-gray-100 rounded-lg hover:bg-gray-200 transition duration-150 ease-in-out">
                    <i class="fas fa-list text-gray-600 mr-2"></i>
                    <span class="text-sm font-medium">All</span>
                </a>
                <a href="{{ route('predictions.over15') }}" class="flex items-center justify-center p-3 {{ $category == 'Over 1.5' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 hover:bg-gray-200' }} rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-arrow-up text-gray-600 mr-2"></i>
                    <span class="text-sm font-medium">Over 1.5</span>
                </a>
                <a href="{{ route('predictions.over25') }}" class="flex items-center justify-center p-3 {{ $category == 'Over 2.5' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 hover:bg-gray-200' }} rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-arrow-up text-gray-600 mr-2"></i>
                    <span class="text-sm font-medium">Over 2.5</span>
                </a>
                <a href="{{ route('predictions.double-chance') }}" class="flex items-center justify-center p-3 {{ $category == 'Double Chance' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 hover:bg-gray-200' }} rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-exchange-alt text-gray-600 mr-2"></i>
                    <span class="text-sm font-medium">Double Chance</span>
                </a>
                <a href="{{ route('predictions.bts') }}" class="flex items-center justify-center p-3 {{ $category == 'Both Teams to Score' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 hover:bg-gray-200' }} rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-futbol text-gray-600 mr-2"></i>
                    <span class="text-sm font-medium">BTS</span>
                </a>
                <a href="{{ route('predictions.draw') }}" class="flex items-center justify-center p-3 {{ $category == 'Draw' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 hover:bg-gray-200' }} rounded-lg transition duration-150 ease-in-out">
                    <i class="fas fa-equals text-gray-600 mr-2"></i>
                    <span class="text-sm font-medium">Draw</span>
                </a>
            </div>
        </div>

        <!-- Predictions by League -->
        @if($fixturesByLeague->count() > 0)
            @foreach($fixturesByLeague as $leagueName => $fixtures)
            <div class="mb-8">
                <div class="flex items-center mb-6">
                    <i class="fas fa-trophy text-yellow-500 text-2xl mr-3"></i>
                    <h2 class="text-xl font-bold text-gray-800">{{ $leagueName }}</h2>
                    <span class="ml-2 bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $fixtures->count() }} matches
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($fixtures as $fixture)
                        @foreach($fixture->predictions as $prediction)
                        <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition duration-300 ease-in-out border border-gray-200">
                            <!-- Match Header -->
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $fixture->home_team }}</h4>
                                    <span class="text-gray-500 text-sm">vs</span>
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $fixture->away_team }}</h4>
                                </div>
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <span>{{ $fixture->match_date->format('M d, Y') }}</span>
                                    <span class="font-medium">{{ $fixture->match_date->format('H:i') }}</span>
                                </div>
                            </div>

                            <!-- Prediction Details -->
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $prediction->category }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        {{ $prediction->tip }}
                                    </span>
                                </div>

                                <!-- 1X2 Odds Display -->
                                <div class="mb-3">
                                    <div class="text-sm text-gray-600 mb-2">1X2 Odds:</div>
                                    <div class="flex justify-between items-center bg-gray-50 rounded-lg p-2">
                                        <div class="text-center">
                                            <div class="text-xs text-gray-500">1</div>
                                            <div class="font-bold text-green-600">{{ $prediction->odds ?? '2.10' }}</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-xs text-gray-500">X</div>
                                            <div class="font-bold text-blue-600">{{ number_format(($prediction->odds ?? 2.10) * 0.8, 2) }}</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-xs text-gray-500">2</div>
                                            <div class="font-bold text-red-600">{{ number_format(($prediction->odds ?? 2.10) * 1.2, 2) }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confidence Bar -->
                                <div class="mb-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm text-gray-600">Confidence</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $prediction->confidence }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: {{ $prediction->confidence }}%"></div>
                                    </div>
                                </div>

                                <!-- Analysis -->
                                @if($prediction->analysis)
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600 italic">"{{ $prediction->analysis }}"</p>
                                </div>
                                @endif

                                <!-- Type Badges -->
                                <div class="flex flex-wrap gap-2">
                                    @if($prediction->is_premium)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-crown mr-1"></i>Premium
                                        </span>
                                    @endif
                                    @if($prediction->is_maxodds)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-star mr-1"></i>Maxodds
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-chart-line mr-1"></i>{{ $prediction->category }}
                                    </span>
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
            <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No {{ $category }} Predictions Found</h3>
            <p class="text-gray-600">Check back later for new {{ $category }} predictions.</p>
        </div>
        @endif

        <!-- Pagination -->
        @if(isset($fixtures) && method_exists($fixtures, 'hasPages') && $fixtures->hasPages())
        <div class="mt-8">
            {{ $fixtures->links() }}
        </div>
        @endif

        <!-- Call to Action -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-xl p-8 mt-12 text-white text-center">
            <h2 class="text-3xl font-bold mb-4">Want More {{ $category }} Predictions?</h2>
            <p class="text-xl mb-6">Get access to exclusive {{ $category }} predictions with higher accuracy rates.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('premium.tips') }}" class="bg-yellow-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-yellow-600 transition duration-150 ease-in-out">
                    <i class="fas fa-crown mr-2"></i>Premium Tips
                </a>
                <a href="{{ route('pricing') }}" class="bg-green-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-600 transition duration-150 ease-in-out">
                    <i class="fas fa-credit-card mr-2"></i>Subscribe Now
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
