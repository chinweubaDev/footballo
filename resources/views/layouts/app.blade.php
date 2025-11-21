<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Football Predictions')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-slate-100 min-h-screen">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('home') }}" class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-futbol text-white text-lg"></i>
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                                        Football Predictions
                                    </h1>
                                    <p class="text-xs text-slate-500 -mt-1">Expert Analysis</p>
                                </div>
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden lg:ml-12 lg:flex lg:space-x-8">
                            <a href="{{ route('home') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-home mr-2"></i>Home
                            </a>
                            <div class="relative group">
                                <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                    <i class="fas fa-chart-line mr-2"></i>Free Predictions
                                    <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                </button>
                                <div class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                    <div class="py-2">
                                        <a href="{{ route('predictions') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-list mr-3 text-primary-500"></i>All Predictions
                                        </a>
                                        <a href="{{ route('predictions.over15') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-arrow-up mr-3 text-green-500"></i>Over 1.5 Goals
                                        </a>
                                        <a href="{{ route('predictions.over25') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-arrow-up mr-3 text-green-500"></i>Over 2.5 Goals
                                        </a>
                                        <a href="{{ route('predictions.double-chance') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-exchange-alt mr-3 text-blue-500"></i>Double Chance
                                        </a>
                                        <a href="{{ route('predictions.bts') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-futbol mr-3 text-orange-500"></i>Both Teams to Score
                                        </a>
                                        <a href="{{ route('predictions.draw') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-equals mr-3 text-gray-500"></i>Draw
                                        </a>
                                    </div>
                                </div>
                            </div>
                          
                            <a href="{{ route('tips.vip') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-crown mr-2 text-blue-500"></i>Premium Tips
                            </a>
                            <a href="{{ route('tips.vvip') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-gem mr-2 text-purple-500"></i>VVIP Tips
                            </a>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('pricing') }}" class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-tags mr-2"></i>Pricing
                            </a>
                            
                            <!-- User Menu -->
                            <div class="relative group">
                                <button class="flex items-center space-x-3 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors duration-200">
                                    <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <div class="hidden md:block text-left">
                                        <p class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-slate-500">
                                            @if(auth()->user()->hasActiveVVIP())
                                                <span class="text-purple-600">VVIP</span>
                                            @elseif(auth()->user()->hasActiveVIP())
                                                <span class="text-blue-600">VIP</span>
                                            @else
                                                <span class="text-slate-500">Free</span>
                                            @endif
                                        </p>
                                    </div>
                                    <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                </button>
                                
                                <div class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                    <div class="py-2">
                                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-tachometer-alt mr-3 text-primary-500"></i>Dashboard
                                        </a>
                                        <a href="{{ route('profile') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-user mr-3 text-slate-500"></i>Profile
                                        </a>
                                        @if(auth()->user()->is_admin)
                                            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                                <i class="fas fa-cog mr-3 text-orange-500"></i>Admin Panel
                                            </a>
                                        @endif
                                        <div class="border-t border-slate-200 my-2"></div>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('pricing') }}" class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-tags mr-2"></i>Pricing
                            </a>
                           
                            <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="fas fa-user-plus mr-2"></i>Login/Register
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="lg:hidden">
                        <button type="button" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="lg:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t border-slate-200">
                    <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-50">Home</a>
                    <a href="{{ route('predictions') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-50">All Predictions</a>
                    <a href="{{ route('match.standings') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-50">Standings</a>
                    <a href="{{ route('match.upcoming') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-50">Upcoming</a>
                    <a href="{{ route('tips.vip') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-50">VIP Tips</a>
                    <a href="{{ route('tips.vvip') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-50">VVIP Tips</a>
                    <a href="{{ route('pricing') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-50">Pricing</a>
                </div>
                <div class="pt-4 pb-3 border-t border-slate-200 bg-slate-50">
                    @auth
                        <div class="flex items-center px-4 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div class="ml-3">
                                <div class="text-base font-medium text-slate-800">{{ auth()->user()->name }}</div>
                                <div class="text-sm text-slate-500">
                                    @if(auth()->user()->hasActiveVVIP())
                                        <span class="text-purple-600">VVIP Member</span>
                                    @elseif(auth()->user()->hasActiveVIP())
                                        <span class="text-blue-600">VIP Member</span>
                                    @else
                                        <span class="text-slate-500">Free Member</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-white rounded-lg mx-2">Dashboard</a>
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-white rounded-lg mx-2">Profile</a>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-white rounded-lg mx-2">Admin Panel</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-red-600 hover:bg-red-50 rounded-lg mx-2">Logout</button>
                            </form>
                        </div>
                    @else
                        <div class="px-4 space-y-2">
                            <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-white rounded-lg">Login</a>
                            <a href="{{ route('register') }}" class="block w-full text-center px-4 py-2 text-base font-medium text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg hover:from-primary-600 hover:to-primary-700">Get Started</a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Brand -->
                    <div class="lg:col-span-1">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-futbol text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Football Predictions</h3>
                                <p class="text-sm text-slate-400">Expert Analysis</p>
                            </div>
                        </div>
                        <p class="text-slate-300 mb-6 leading-relaxed">Your trusted source for accurate football predictions and expert betting tips. Join thousands of successful bettors.</p>
                        <div class="flex space-x-4">
                            <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-slate-700 hover:bg-primary-600 rounded-lg flex items-center justify-center transition-colors duration-200">
                                <i class="fab fa-telegram"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-lg font-semibold mb-6">Quick Links</h4>
                        <ul class="space-y-3">
                            <li><a href="{{ route('home') }}" class="text-slate-300 hover:text-white transition-colors duration-200">Home</a></li>
                            <li><a href="{{ route('predictions') }}" class="text-slate-300 hover:text-white transition-colors duration-200">All Predictions</a></li>
                            <li><a href="{{ route('tips.vip') }}" class="text-slate-300 hover:text-white transition-colors duration-200">VIP Tips</a></li>
                            <li><a href="{{ route('tips.vvip') }}" class="text-slate-300 hover:text-white transition-colors duration-200">VVIP Tips</a></li>
                            <li><a href="{{ route('pricing') }}" class="text-slate-300 hover:text-white transition-colors duration-200">Pricing</a></li>
                        </ul>
                    </div>

                    <!-- Categories -->
                    <div>
                        <h4 class="text-lg font-semibold mb-6">Categories</h4>
                        <ul class="space-y-3">
                            <li><a href="{{ route('predictions.over15') }}" class="text-slate-300 hover:text-white transition-colors duration-200">Over 1.5 Goals</a></li>
                            <li><a href="{{ route('predictions.over25') }}" class="text-slate-300 hover:text-white transition-colors duration-200">Over 2.5 Goals</a></li>
                            <li><a href="{{ route('predictions.double-chance') }}" class="text-slate-300 hover:text-white transition-colors duration-200">Double Chance</a></li>
                            <li><a href="{{ route('predictions.bts') }}" class="text-slate-300 hover:text-white transition-colors duration-200">Both Teams to Score</a></li>
                            <li><a href="{{ route('predictions.draw') }}" class="text-slate-300 hover:text-white transition-colors duration-200">Draw Predictions</a></li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h4 class="text-lg font-semibold mb-6">Support</h4>
                        <ul class="space-y-3">
                            <li><a href="#" class="text-slate-300 hover:text-white transition-colors duration-200">Contact Us</a></li>
                            <li><a href="#" class="text-slate-300 hover:text-white transition-colors duration-200">FAQ</a></li>
                            <li><a href="#" class="text-slate-300 hover:text-white transition-colors duration-200">Terms of Service</a></li>
                            <li><a href="#" class="text-slate-300 hover:text-white transition-colors duration-200">Privacy Policy</a></li>
                            <li><a href="#" class="text-slate-300 hover:text-white transition-colors duration-200">Refund Policy</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="border-t border-slate-700 mt-12 pt-8">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-slate-400 text-sm">&copy; {{ date('Y') }} Football Predictions. All rights reserved.</p>
                        <div class="flex items-center space-x-6 mt-4 md:mt-0">
                            <span class="text-slate-400 text-sm">Trusted by 10,000+ users</span>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-slate-400 text-sm">Live Support</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>
