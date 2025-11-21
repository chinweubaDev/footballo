@extends('layouts.app')

@section('title', 'All Predictions - Football Predictions')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">All Predictions</h1>
                    <p class="text-gray-600">Browse all available football predictions and tips</p>
                </div>
                <div class="flex space-x-2">
                    <select id="leagueFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">All Leagues</option>
                        <option value="Premier League">Premier League</option>
                        <option value="La Liga">La Liga</option>
                        <option value="Bundesliga">Bundesliga</option>
                        <option value="Serie A">Serie A</option>
                        <option value="Ligue 1">Ligue 1</option>
                    </select>
                    <select id="categoryFilter" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">All Categories</option>
                        <option value="1X2">1X2</option>
                        <option value="Over/Under">Over/Under</option>
                        <option value="Both Teams to Score">Both Teams to Score</option>
                        <option value="Double Chance">Double Chance</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Predictions by League -->
        @if($fixturesByLeague->count() > 0)
            @foreach($fixturesByLeague as $leagueName => $fixtures)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
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
            <i class="fas fa-futbol text-gray-300 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No Predictions Available</h2>
            <p class="text-gray-600 mb-6">Check back later for new predictions and tips.</p>
            <a href="{{ route('home') }}" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                Go to Homepage
            </a>
        </div>
        @endif

        <!-- Pagination -->
        @if(isset($fixtures) && method_exists($fixtures, 'hasPages') && $fixtures->hasPages())
        <div class="mt-8">
            {{ $fixtures->links() }}
        </div>
        @endif

        <!-- Call to Action -->
        <div class="bg-gradient-to-r from-green-600 to-green-800 rounded-lg shadow-xl p-8 mt-12 text-white text-center">
            <h2 class="text-3xl font-bold mb-4">Want Premium Predictions?</h2>
            <p class="text-xl mb-6">Get access to exclusive predictions with higher accuracy rates.</p>
            <a href="{{ route('pricing') }}" class="bg-white text-green-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-150 ease-in-out">
                View Premium Plans
            </a>
        </div>
    </div>
</div>

<!-- Prediction filtering JavaScript is now handled by app.js -->
@endsection
