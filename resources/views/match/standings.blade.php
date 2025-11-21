@extends('layouts.app')

@section('title', 'League Standings - Football Predictions')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">League Standings</h1>
                    <p class="text-gray-600">Current league table and team positions</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('match.upcoming') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out">
                        <i class="fas fa-calendar mr-2"></i>Upcoming Matches
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <form method="GET" action="{{ route('match.standings') }}" class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-2">League</label>
                    <select name="league" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        @foreach($leagues as $id => $name)
                            <option value="{{ $id }}" {{ $selectedLeague == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex-1 min-w-32">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Season</label>
                    <select name="season" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        @for($year = date('Y'); $year >= 2020; $year--)
                            <option value="{{ $year }}" {{ $selectedSeason == $year ? 'selected' : '' }}>{{ $year }}/{{ $year + 1 }}</option>
                        @endfor
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition duration-150 ease-in-out">
                        <i class="fas fa-search mr-2"></i>Update
                    </button>
                </div>
            </form>
        </div>

        <!-- Standings Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    @if(isset($standings['response']) && count($standings['response']) > 0)
                        {{ $standings['response'][0]['league']['name'] }} - {{ $selectedSeason }}/{{ $selectedSeason + 1 }}
                    @else
                        League Standings
                    @endif
                </h2>
            </div>
            
            @if(isset($standings['response']) && count($standings['response']) > 0)
                @foreach($standings['response'] as $league)
                    @if(isset($league['league']['standings']) && count($league['league']['standings']) > 0)
                        @foreach($league['league']['standings'] as $group)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pos</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">P</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">W</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">D</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">L</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">GF</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">GA</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">GD</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pts</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Form</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($group as $team)
                                        <tr class="hover:bg-gray-50 {{ $team['rank'] <= 4 ? 'bg-green-50' : ($team['rank'] >= count($group) - 2 ? 'bg-red-50' : '') }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $team['rank'] <= 4 ? 'bg-green-100 text-green-800' : ($team['rank'] >= count($group) - 2 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ $team['rank'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <img class="h-8 w-8 rounded-full" src="{{ $team['team']['logo'] }}" alt="{{ $team['team']['name'] }}" onerror="this.src='https://via.placeholder.com/32x32?text=⚽'">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $team['team']['name'] }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $team['all']['played'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $team['all']['win'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $team['all']['draw'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $team['all']['lose'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $team['all']['goals']['for'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">{{ $team['all']['goals']['against'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 {{ $team['goalsDiff'] > 0 ? 'text-green-600' : ($team['goalsDiff'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                                {{ $team['goalsDiff'] > 0 ? '+' : '' }}{{ $team['goalsDiff'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">{{ $team['points'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                                <div class="flex space-x-1">
                                                    @foreach(str_split($team['form']) as $result)
                                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-medium
                                                            {{ $result === 'W' ? 'bg-green-100 text-green-800' : 
                                                               ($result === 'D' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                            {{ $result }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @else
                        <div class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-table text-4xl mb-4"></i>
                                <p class="text-lg">No standings data available</p>
                                <p class="text-sm">Please try a different league or season</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <i class="fas fa-table text-4xl mb-4"></i>
                        <p class="text-lg">No standings data available</p>
                        <p class="text-sm">Please try a different league or season</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Legend -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Legend</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-green-100 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700">Champions League / Europa League</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-red-100 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700">Relegation Zone</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gray-100 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700">Mid Table</span>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">
                    <strong>Form:</strong> W = Win, D = Draw, L = Loss (Last 5 matches)
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

