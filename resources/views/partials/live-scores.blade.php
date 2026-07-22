<!-- Live Scores Widget - Polls every 60 seconds -->
<div x-data="liveScores()" x-init="fetchScores(); setInterval(() => fetchScores(), 60000)" class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4 flex items-center justify-between">
        <h3 class="text-white font-bold flex items-center">
            <span class="relative flex h-3 w-3 mr-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
            </span>
            Live Scores
        </h3>
        <span class="text-green-100 text-xs" x-text="lastUpdated"></span>
    </div>

    <div class="divide-y divide-slate-100">
        <template x-if="fixtures.length === 0">
            <div class="p-8 text-center text-slate-400">
                <i class="fas fa-futbol text-4xl mb-3 opacity-30"></i>
                <p>No live matches right now.<br>Check back soon!</p>
            </div>
        </template>

        <template x-for="match in fixtures" :key="match.fixture.id">
            <div class="p-4 hover:bg-slate-50 transition-colors">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400 font-medium" x-text="match.league.name"></span>
                    <span class="text-xs font-bold text-green-600 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                        <span x-text="match.fixture.status.elapsed + '\'"></span>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex-1 text-right mr-3">
                        <span class="text-sm font-semibold text-slate-800" x-text="match.teams.home.name"></span>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="text-xl font-black text-slate-900 bg-slate-100 px-3 py-1 rounded-lg" 
                              x-text="match.goals.home + ' - ' + match.goals.away"></span>
                    </div>
                    <div class="flex-1 ml-3">
                        <span class="text-sm font-semibold text-slate-800" x-text="match.teams.away.name"></span>
                    </div>
                </div>
                <div class="flex justify-center mt-1.5" x-show="match.events && match.events.length > 0">
                    <template x-for="event in match.events.slice(-3)" :key="event.time.elapsed">
                        <span class="text-xs text-slate-500 mx-2" 
                              x-text="event.player.name + ' ⚽ ' + event.time.elapsed + '\'"></span>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-center">
        <a href="{{ route('match.upcoming') }}" class="text-sm text-green-600 hover:text-green-700 font-medium">
            View All Matches <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
</div>

<script>
function liveScores() {
    return {
        fixtures: [],
        lastUpdated: '',
        async fetchScores() {
            try {
                const res = await fetch('/live-scores');
                const data = await res.json();
                this.fixtures = data.fixtures || [];
                this.lastUpdated = 'Updated ' + new Date().toLocaleTimeString();
            } catch (e) {
                console.error('Live scores fetch error:', e);
            }
        }
    }
}
</script>
