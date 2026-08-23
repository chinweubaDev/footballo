<nav class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('admin.predictions') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
    </a>
    <a href="{{ route('admin.predictions.list') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.list') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-list mr-1"></i> Predictions
    </a>
    <a href="{{ route('admin.predictions.leagues') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.leagues') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-trophy mr-1"></i> Leagues
    </a>
    <a href="{{ route('admin.predictions.markets') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.markets') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-sliders-h mr-1"></i> Markets
    </a>
    <a href="{{ route('admin.predictions.performance') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.performance') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-chart-bar mr-1"></i> Performance
    </a>
    <a href="{{ route('admin.predictions.live-validation') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.live-validation') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-balance-scale mr-1"></i> Live Validation
    </a>
    <a href="{{ route('admin.predictions.live-validation.report') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.live-validation.report') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-file-alt mr-1"></i> Daily Report
    </a>
    <a href="{{ route('admin.predictions.performance.markets') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.performance.markets') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-chart-pie mr-1"></i> Markets
    </a>
    <a href="{{ route('admin.predictions.performance.leagues') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.performance.leagues') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-flag mr-1"></i> Leagues
    </a>
    <a href="{{ route('admin.predictions.performance.matrix') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.performance.matrix') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-th-large mr-1"></i> Matrix
    </a>
    <a href="{{ route('admin.predictions.performance.export') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.performance.export') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-download mr-1"></i> Export
    </a>
    <a href="{{ route('admin.predictions.backtesting.index') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.backtesting.*') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-flask mr-1"></i> Backtesting
    </a>
    <a href="{{ route('admin.predictions.models') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.models*') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-cubes mr-1"></i> Models
    </a>
    <a href="{{ route('admin.predictions.validation.matrix') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.validation.matrix') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-table mr-1"></i> Matrix
    </a>
    <a href="{{ route('admin.predictions.validation.multi-season') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.validation.multi-season') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-layer-group mr-1"></i> Multi-Season
    </a>
    <a href="{{ route('admin.predictions.validation.ranking') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.validation.ranking') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-sort-amount-down mr-1"></i> Ranking
    </a>
    <a href="{{ route('admin.predictions.validation.candidates') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.validation.candidates*') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-check-double mr-1"></i> Candidates
    </a>
</nav>

@if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
@endif
