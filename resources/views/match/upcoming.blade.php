@extends('layouts.app')

@section('title', 'Upcoming Matches - Football Predictions')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Upcoming Matches</h1>
                    <p class="text-gray-600">Scheduled matches and fixtures</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('match.standings') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                        <i class="fas fa-table mr-2"></i>League Standings
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <form method="GET" action="{{ route('match.upcoming') }}" class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-2">League</label>
                    <select name="league" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        @foreach($leagues as $id => $name)
                            <option value="{{ $id }}" {{ $selectedLeague == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex-1 min-w-32">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                    <input type="date" name="date" value="{{ $selectedDate }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" name="action" value="today" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150 ease-in-out">
                        Today
                    </button>
                    <button type="submit" name="action" value="tomorrow" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition duration-150 ease-in-out">
                        Tomorrow
                    </button>
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition duration-150 ease-in-out">
                        <i class="fas fa-search mr-2"></i>Update
                    </button>
                </div>
            </form>
        </div>

        <!-- Matches -->
        @if(isset($matches['response']) && count($matches['response']) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($matches['response'] as $match)
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition duration-300 ease-in-out border border-gray-200">
                    <!-- Match Header -->
                    <div class="bg-gradient-to-r from-green-500 to-blue-600 text-white p-4 rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium">
                                {{ $match['league']['name'] }}
                            </div>
                            <div class="text-sm">
                                {{ \Carbon\Carbon::parse($match['fixture']['date'])->format('M d, Y') }}
                            </div>
                        </div>
                        <div class="text-xs mt-1 opacity-90">
                            {{ \Carbon\Carbon::parse($match['fixture']['date'])->format('H:i') }} {{ $match['fixture']['timezone'] }}
                        </div>
                    </div>

                    <!-- Teams -->
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $match['teams']['home']['logo'] }}" alt="{{ $match['teams']['home']['name'] }}" 
                                     class="w-8 h-8 rounded-full" onerror="this.src='https://via.placeholder.com/32x32?text=⚽'">
                                <span class="font-semibold text-gray-900">{{ $match['teams']['home']['name'] }}</span>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-400">VS</div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="font-semibold text-gray-900">{{ $match['teams']['away']['name'] }}</span>
                                <img src="{{ $match['teams']['away']['logo'] }}" alt="{{ $match['teams']['away']['name'] }}" 
                                     class="w-8 h-8 rounded-full" onerror="this.src='https://via.placeholder.com/32x32?text=⚽'">
                            </div>
                        </div>

                        <!-- Match Details -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Status:</span>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                    {{ $match['fixture']['status']['long'] }}
                                </span>
                            </div>
                            
                            @if($match['fixture']['venue']['name'])
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Venue:</span>
                                <span>{{ $match['fixture']['venue']['name'] }}</span>
                            </div>
                            @endif

                            @if($match['fixture']['referee'])
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Referee:</span>
                                <span>{{ $match['fixture']['referee'] }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Odds (if available) -->
                        @if(isset($match['odds']) && count($match['odds']) > 0)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Match Odds</h4>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($match['odds'] as $odd)
                                    @if(isset($odd['bookmakers']) && count($odd['bookmakers']) > 0)
                                        @php $bookmaker = $odd['bookmakers'][0]; @endphp
                                        @if(isset($bookmaker['bets']) && count($bookmaker['bets']) > 0)
                                            @foreach($bookmaker['bets'] as $bet)
                                                @if($bet['name'] === 'Match Winner')
                                                    <div class="text-center">
                                                        <div class="text-xs text-gray-500">{{ $bet['values'][0]['value'] ?? '1' }}</div>
                                                        <div class="font-bold text-lg">{{ $bet['values'][0]['odd'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-xs text-gray-500">Draw</div>
                                                        <div class="font-bold text-lg">{{ $bet['values'][1]['odd'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="text-xs text-gray-500">{{ $bet['values'][2]['value'] ?? '2' }}</div>
                                                        <div class="font-bold text-lg">{{ $bet['values'][2]['odd'] ?? 'N/A' }}</div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Match Actions -->
                    <div class="px-6 py-4 bg-gray-50 rounded-b-lg">
                        <div class="flex justify-between items-center">
                            <div class="text-xs text-gray-500">
                                Match ID: {{ $match['fixture']['id'] }}
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <i class="fas fa-chart-line mr-1"></i>Stats
                                </button>
                                <button class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                <div class="text-gray-500">
                    <i class="fas fa-calendar-times text-4xl mb-4"></i>
                    <p class="text-lg">No upcoming matches found</p>
                    <p class="text-sm">Try selecting a different league or date</p>
                </div>
            </div>
        @endif

        <!-- Quick Date Navigation -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Date Navigation</h3>
            <div class="flex flex-wrap gap-2">
                @php
                    $dates = [];
                    for($i = -3; $i <= 7; $i++) {
                        $dates[] = \Carbon\Carbon::now()->addDays($i);
                    }
                @endphp
                @foreach($dates as $date)
                    <a href="{{ route('match.upcoming', ['league' => $selectedLeague, 'date' => $date->format('Y-m-d')]) }}" 
                       class="px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out
                              {{ $selectedDate === $date->format('Y-m-d') ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        @if($date->isToday())
                            Today
                        @elseif($date->isTomorrow())
                            Tomorrow
                        @else
                            {{ $date->format('M d') }}
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

