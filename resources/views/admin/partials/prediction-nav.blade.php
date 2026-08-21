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
    <a href="{{ route('admin.predictions.backtesting.index') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.backtesting.*') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-flask mr-1"></i> Backtesting
    </a>
    <a href="{{ route('admin.predictions.models') }}"
       class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.predictions.models*') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
        <i class="fas fa-cubes mr-1"></i> Models
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
