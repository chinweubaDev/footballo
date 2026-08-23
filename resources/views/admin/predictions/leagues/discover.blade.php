@extends('layouts.app')

@section('title', 'Discover Leagues - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Discover &amp; Add Leagues</h1>
            <p class="text-gray-600">Browse leagues by country, search, then add and enable the ones you want.</p>
        </div>

        @include('admin.partials.prediction-nav')

        @if(session('success'))
            <div class="mb-6 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Filters (client-side, instant) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 sticky top-0 z-10">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="league-search" placeholder="Search league (e.g. Premier League, Serie A, Brazil...)" class="w-full border border-gray-300 rounded-lg pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-green-500">
                </div>
                <select id="league-country" class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white sm:w-56">
                    <option value="">All countries</option>
                    @foreach($catalog as $group)
                        <option value="{{ $group['country'] }}">{{ $group['country'] }}</option>
                    @endforeach
                </select>
                <a href="{{ route('admin.predictions.leagues') }}" class="px-4 py-2.5 rounded-lg text-sm font-semibold bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200 whitespace-nowrap">Back</a>
            </div>
            <p class="text-xs text-gray-500 mt-3">
                <span class="font-semibold text-gray-700" id="catalog-count">{{ count($catalog) }}</span> countries ·
                <span class="font-semibold text-gray-700" id="league-count">0</span> leagues shown.
            </p>
        </div>

        {{-- Catalog --}}
        <div id="catalog" class="space-y-6">
            @foreach($catalog as $group)
            <div class="country-group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" data-country="{{ $group['country'] }}">
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 flex items-center gap-3">
                    @if(($group['code'] ?? '') === 'world')
                        <i class="fas fa-globe text-gray-500"></i>
                    @else
                        <img src="https://media.api-sports.io/flags/{{ $group['code'] }}.svg" alt="{{ $group['country'] }}" class="w-5 h-4 object-cover rounded-sm border border-gray-200" onerror="this.style.display='none'">
                    @endif
                    <h2 class="font-bold text-gray-800">{{ $group['country'] }}</h2>
                    <span class="text-xs text-gray-400">{{ count($group['leagues']) }} league(s)</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($group['leagues'] as $league)
                    @php
                        $imported = in_array((int) $league['id'], $importedIds, true);
                    @endphp
                    <div class="league-row flex items-center justify-between gap-4 px-6 py-3.5 hover:bg-gray-50" data-name="{{ Str::lower($league['name']) }}" data-country="{{ $group['country'] }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="https://media.api-sports.io/football/leagues/{{ $league['id'] }}.png" alt="{{ $league['name'] }}" class="w-9 h-9 object-contain flex-shrink-0" onerror="this.outerHTML='<div class=\'w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0\'><i class=\'fas fa-trophy text-gray-400\'></i></div>'">
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $league['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $league['type'] }} · API ID: {{ $league['id'] }}</p>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            @if($imported)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Already added</span>
                            @else
                                <form method="POST" action="{{ route('admin.predictions.leagues.import') }}">
                                    @csrf
                                    <input type="hidden" name="api_football_league_id" value="{{ $league['id'] }}">
                                    <input type="hidden" name="name" value="{{ $league['name'] }}">
                                    <input type="hidden" name="country" value="{{ $group['country'] }}">
                                    <input type="hidden" name="logo" value="https://media.api-sports.io/football/leagues/{{ $league['id'] }}.png">
                                    <input type="hidden" name="season" value="{{ $season }}">
                                    <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold bg-green-600 text-white hover:bg-green-700">
                                        <i class="fas fa-plus mr-1"></i> Add &amp; Enable
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div id="no-results" class="hidden bg-slate-50 rounded-xl border-2 border-dashed border-slate-200 p-12 text-center text-gray-500">
            <i class="fas fa-search text-4xl mb-4 block opacity-40"></i>
            <p class="text-lg font-semibold text-gray-600">No leagues match your search</p>
            <p class="text-sm text-gray-400 mt-1">Try a different name or clear the country filter.</p>
        </div>

        {{-- Live API-Football search (optional, only when API quota available) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-10">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Search API-Football live</h2>
            <p class="text-sm text-gray-500 mb-4">Use this to find leagues not listed above. Requires available API quota.</p>
            <form method="GET" action="{{ route('admin.predictions.leagues.discover') }}" class="flex gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="e.g. Championship, Copa Libertadores, Vietnam..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm">
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-semibold hover:bg-green-700">
                    <i class="fas fa-cloud-download-alt mr-1"></i> Search Live
                </button>
            </form>

            @if($apiError)
                <div class="mt-4 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ $apiError }}
                    <span class="block text-amber-700 text-xs mt-1">You can still add leagues from the catalog above.</span>
                </div>
            @endif

            @if($search !== '' && count($apiResults) > 0)
                <div class="mt-5 divide-y divide-gray-100 border-t border-gray-100">
                    @foreach($apiResults as $result)
                    @php
                        $apiId = (int) ($result['league']['id'] ?? 0);
                        $name = $result['league']['name'] ?? 'Unknown';
                        $country = $result['country']['name'] ?? '';
                        $logo = $result['league']['logo'] ?? null;
                        $season = (int) ($result['seasons'][0]['year'] ?? 2025);
                        $imported = in_array($apiId, $importedIds, true);
                    @endphp
                    <div class="flex items-center justify-between gap-4 py-4">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($logo)
                                <img src="{{ $logo }}" alt="{{ $name }}" class="w-10 h-10 object-contain flex-shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-trophy text-gray-400"></i></div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $name }}</p>
                                <p class="text-xs text-gray-500">{{ $country }} · API ID: {{ $apiId }} · Season: {{ $season }}</p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            @if($imported)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Already added</span>
                            @else
                                <form method="POST" action="{{ route('admin.predictions.leagues.import') }}">
                                    @csrf
                                    <input type="hidden" name="api_football_league_id" value="{{ $apiId }}">
                                    <input type="hidden" name="name" value="{{ $name }}">
                                    <input type="hidden" name="country" value="{{ $country }}">
                                    <input type="hidden" name="logo" value="{{ $logo }}">
                                    <input type="hidden" name="season" value="{{ $season }}">
                                    <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold bg-green-600 text-white hover:bg-green-700">
                                        <i class="fas fa-plus mr-1"></i> Add &amp; Enable
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        var searchInput = document.getElementById('league-search');
        var countrySelect = document.getElementById('league-country');
        var rows = document.querySelectorAll('.league-row');
        var groups = document.querySelectorAll('.country-group');
        var noResults = document.getElementById('no-results');
        var leagueCount = document.getElementById('league-count');
        var countryCount = document.getElementById('catalog-count');

        function applyFilters() {
            var query = (searchInput.value || '').trim().toLowerCase();
            var country = countrySelect.value || '';
            var visible = 0;

            rows.forEach(function (row) {
                var name = row.getAttribute('data-name') || '';
                var rowCountry = row.getAttribute('data-country') || '';
                var matchesQuery = query === '' || name.indexOf(query) !== -1;
                var matchesCountry = country === '' || rowCountry === country;

                if (matchesQuery && matchesCountry) {
                    row.classList.remove('hidden');
                    visible++;
                } else {
                    row.classList.add('hidden');
                }
            });

            var visibleCountries = 0;
            groups.forEach(function (group) {
                var anyVisible = Array.prototype.some.call(group.querySelectorAll('.league-row'), function (r) {
                    return !r.classList.contains('hidden');
                });
                if (anyVisible) {
                    group.classList.remove('hidden');
                    visibleCountries++;
                } else {
                    group.classList.add('hidden');
                }
            });

            if (leagueCount) leagueCount.textContent = visible;
            if (countryCount) countryCount.textContent = visibleCountries;
            if (noResults) noResults.classList.toggle('hidden', visible !== 0);
        }

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (countrySelect) countrySelect.addEventListener('change', applyFilters);

        applyFilters();
    })();
</script>
@endsection
