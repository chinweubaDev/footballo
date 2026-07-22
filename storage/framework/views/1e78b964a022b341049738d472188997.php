<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="author" content="EsureBet">
    <meta name="theme-color" content="#16a34a">

    
    <title><?php echo $__env->yieldContent('title', 'EsureBet — Best Football Prediction Site | Sure Tips & Winning Predictions'); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', 'EsureBet provides accurate football predictions, sure betting tips, over 1.5/2.5 goals, BTTS, double chance, and expert analysis. Win with 85%+ accuracy rate.'); ?>">
    <meta name="keywords" content="<?php echo $__env->yieldContent('meta_keywords', 'football predictions, sure tips, betting tips, over 2.5 predictions, BTTS tips, double chance, soccer predictions, today football tips, accurate football prediction'); ?>">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="canonical" href="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">
    <link rel="alternate" hreflang="en" href="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">

    
    <meta property="og:site_name" content="EsureBet">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $__env->yieldContent('og_url', url()->current()); ?>">
    <meta property="og:title" content="<?php echo $__env->yieldContent('og_title', 'EsureBet — Accurate Football Predictions & Sure Betting Tips'); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('og_description', 'Expert football predictions with 85%+ accuracy. Daily tips, over 2.5 goals, BTTS, double chance, and VIP predictions.'); ?>">
    <meta property="og:image" content="<?php echo $__env->yieldContent('og_image', asset('logo.png')); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_US">

    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@esurebet">
    <meta name="twitter:title" content="<?php echo $__env->yieldContent('og_title', 'EsureBet — Accurate Football Predictions'); ?>">
    <meta name="twitter:description" content="<?php echo $__env->yieldContent('og_description', 'Expert football predictions with 85%+ accuracy.'); ?>">
    <meta name="twitter:image" content="<?php echo $__env->yieldContent('og_image', asset('logo.png')); ?>">

    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="https://media.api-sports.io">

    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: {50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#14532d'},
                        secondary: {50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a'}
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>?v=<?php echo e(md5_file(public_path('css/app.css'))); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "EsureBet",
        "url": "<?php echo e(url('/')); ?>",
        "logo": "<?php echo e(asset('logo.png')); ?>",
        "description": "Professional football prediction website providing accurate betting tips, match analysis, and sure predictions.",
        "sameAs": [],
        "contactPoint": {
            "@type": "ContactPoint",
            "email": "support@esurebet.com",
            "contactType": "customer support"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "EsureBet",
        "url": "<?php echo e(url('/')); ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo e(url('/predictions')); ?>?search={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <?php echo $__env->yieldPushContent('schema'); ?>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-slate-100 min-h-screen">
    
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-xl">Skip to content</a>

    <div class="min-h-screen">
        <header>
        <nav class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50" aria-label="Main navigation">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="<?php echo e(route('home')); ?>" class="flex items-center space-x-3" aria-label="EsureBet Home">
                                <img src="<?php echo e(asset('logo.png')); ?>" alt="EsureBet Logo" class="w-40 md:w-52 h-auto" width="208" height="40">
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden lg:ml-12 lg:flex lg:space-x-8">
                            <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-home mr-2"></i>Home
                            </a>
                            <div class="relative group">
                                <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                    <i class="fas fa-chart-line mr-2"></i>Free Predictions
                                    <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                </button>
                                <div class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                    <div class="py-2">
                                        <a href="<?php echo e(route('predictions')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-list mr-3 text-primary-500"></i>All Predictions
                                        </a>
                                        <a href="<?php echo e(route('predictions.over15')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-arrow-up mr-3 text-green-500"></i>Over 1.5 Goals
                                        </a>
                                        <a href="<?php echo e(route('predictions.over25')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-arrow-up mr-3 text-green-500"></i>Over 2.5 Goals
                                        </a>
                                        <a href="<?php echo e(route('predictions.double-chance')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-exchange-alt mr-3 text-blue-500"></i>Double Chance
                                        </a>
                                        <a href="<?php echo e(route('predictions.bts')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-futbol mr-3 text-orange-500"></i>Both Teams to Score
                                        </a>
                                        <a href="<?php echo e(route('predictions.draw')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-equals mr-3 text-gray-500"></i>Draw
                                        </a>
                                    </div>
                                </div>
                            </div>
                          
                            <a href="/predictions/premium" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-crown mr-2 text-blue-500"></i>Premium Tips
                            </a>
                            <!-- <a href="<?php echo e(route('tips.vvip')); ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-gem mr-2 text-purple-500"></i>VVIP Tips
                            </a> -->
                            <a href="<?php echo e(route('basketball')); ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-basketball-ball mr-2 text-orange-500"></i>Basketball
                            </a>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        <?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('pricing')); ?>" class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-tags mr-2"></i>Pricing
                            </a>
                            
                            <!-- User Menu -->
                            <div class="relative group">
                                <button class="flex items-center space-x-3 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 transition-colors duration-200">
                                    <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <div class="hidden md:block text-left">
                                        <p class="text-sm font-medium text-slate-800"><?php echo e(auth()->user()->name); ?></p>
                                        <p class="text-xs text-slate-500">
                                            <?php if(auth()->user()->hasActiveVVIP()): ?>
                                                <span class="text-purple-600">VVIP</span>
                                            <?php elseif(auth()->user()->hasActiveVIP()): ?>
                                                <span class="text-blue-600">VIP</span>
                                            <?php else: ?>
                                                <span class="text-slate-500">Free</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                                </button>
                                
                                <div class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                    <div class="py-2">
                                        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-tachometer-alt mr-3 text-primary-500"></i>Dashboard
                                        </a>
                                        <a href="<?php echo e(route('profile')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-user mr-3 text-slate-500"></i>Profile
                                        </a>
                                        <?php if(auth()->user()->is_admin): ?>
                                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                                <i class="fas fa-cog mr-3 text-orange-500"></i>Admin Panel
                                            </a>
                                        <?php endif; ?>
                                        <div class="border-t border-slate-200 my-2"></div>
                                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo e(route('pricing')); ?>" class="hidden md:inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                <i class="fas fa-tags mr-2"></i>Pricing
                            </a>
                           
                            <a href="<?php echo e(route('register')); ?>" class="inline-flex items-center px-4 md:px-6 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="fas fa-user-plus md:mr-2"></i>
                                <span class="hidden md:inline">Login/Register</span>
                                <span class="md:hidden ml-2">Login</span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="lg:hidden flex items-center space-x-2">
                        <?php if(auth()->guard()->check()): ?>
                            <div class="md:hidden w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-xs"></i>
                            </div>
                        <?php endif; ?>
                        <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <i class="fas fa-bars h-6 w-6 flex items-center justify-center text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="lg:hidden hidden animate-fade-in-down" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t border-slate-100 shadow-xl">
                    <a href="<?php echo e(route('home')); ?>" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-slate-700 hover:text-primary-600 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-home mr-3 text-slate-400"></i>Home
                    </a>
                       <div class="relative group">
                                <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors duration-200">
                                    <i class="fas fa-chart-line mr-2"></i>Free Predictions
                                    <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                </button>
                                <div class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                    <div class="py-2">
                                        <a href="<?php echo e(route('predictions')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-list mr-3 text-primary-500"></i>All Predictions
                                        </a>
                                        <a href="<?php echo e(route('predictions.over15')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-arrow-up mr-3 text-green-500"></i>Over 1.5 Goals
                                        </a>
                                        <a href="<?php echo e(route('predictions.over25')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-arrow-up mr-3 text-green-500"></i>Over 2.5 Goals
                                        </a>
                                        <a href="<?php echo e(route('predictions.double-chance')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-exchange-alt mr-3 text-blue-500"></i>Double Chance
                                        </a>
                                        <a href="<?php echo e(route('predictions.bts')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-futbol mr-3 text-orange-500"></i>Both Teams to Score
                                        </a>
                                        <a href="<?php echo e(route('predictions.draw')); ?>" class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <i class="fas fa-equals mr-3 text-gray-500"></i>Draw
                                        </a>
                                    </div>
                                </div>
                            </div>
                    <a href="/predictions/premium" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-slate-700 hover:text-primary-600 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-crown mr-3 text-blue-500"></i>Premium Tips
                    </a>
                    <!-- <a href="<?php echo e(route('tips.vvip')); ?>" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-slate-700 hover:text-primary-600 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-gem mr-3 text-purple-500"></i>VVIP Tips
                    </a> -->
                    <a href="<?php echo e(route('pricing')); ?>" class="flex items-center px-4 py-3 rounded-xl text-base font-semibold text-slate-700 hover:text-primary-600 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-tags mr-3 text-slate-400"></i>Pricing
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-slate-200 bg-slate-50">
                    <?php if(auth()->guard()->check()): ?>
                        <div class="flex items-center px-4 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div class="ml-3">
                                <div class="text-base font-medium text-slate-800"><?php echo e(auth()->user()->name); ?></div>
                                <div class="text-sm text-slate-500">
                                    <?php if(auth()->user()->hasActiveVVIP()): ?>
                                        <span class="text-purple-600">VVIP Member</span>
                                    <?php elseif(auth()->user()->hasActiveVIP()): ?>
                                        <span class="text-blue-600">VIP Member</span>
                                    <?php else: ?>
                                        <span class="text-slate-500">Free Member</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <a href="<?php echo e(route('dashboard')); ?>" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-white rounded-lg mx-2">Dashboard</a>
                            <a href="<?php echo e(route('profile')); ?>" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-white rounded-lg mx-2">Profile</a>
                            <?php if(auth()->user()->is_admin): ?>
                                <a href="<?php echo e(route('admin.dashboard')); ?>" class="block px-4 py-2 text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-white rounded-lg mx-2">Admin Panel</a>
                            <?php endif; ?>
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="block w-full text-left px-4 py-2 text-base font-medium text-red-600 hover:bg-red-50 rounded-lg mx-2">Logout</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="px-4 space-y-2">
                            <a href="<?php echo e(route('login')); ?>" class="block w-full text-center px-4 py-2 text-base font-medium text-slate-700 hover:text-primary-600 hover:bg-white rounded-lg">Login</a>
                            <a href="<?php echo e(route('register')); ?>" class="block w-full text-center px-4 py-2 text-base font-medium text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg hover:from-primary-600 hover:to-primary-700">Get Started</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline"><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline"><?php echo e(session('error')); ?></span>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
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
                            <li><a href="<?php echo e(route('home')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Home</a></li>
                            <li><a href="<?php echo e(route('predictions')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">All Predictions</a></li>
                            <li><a href="<?php echo e(route('tips.vip')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">VIP Tips</a></li>
                            <li><a href="<?php echo e(route('tips.vvip')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">VVIP Tips</a></li>
                            <li><a href="<?php echo e(route('predictions.tomorrow')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Tomorrow's Tips</a></li>
                            <li><a href="<?php echo e(route('pricing')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Pricing</a></li>
                        </ul>
                    </div>

                    <!-- Categories -->
                    <div>
                        <h4 class="text-lg font-semibold mb-6">Categories</h4>
                        <ul class="space-y-3">
                            <li><a href="<?php echo e(route('predictions.over15')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Over 1.5 Goals</a></li>
                            <li><a href="<?php echo e(route('predictions.over25')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Over 2.5 Goals</a></li>
                            <li><a href="<?php echo e(route('predictions.double-chance')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Double Chance</a></li>
                            <li><a href="<?php echo e(route('predictions.bts')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Both Teams to Score</a></li>
                            <li><a href="<?php echo e(route('predictions.draw')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Draw Predictions</a></li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h4 class="text-lg font-semibold mb-6">Support</h4>
                        <ul class="space-y-3">
                            <li><a href="<?php echo e(route('contact')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Contact Us</a></li>
                            <li><a href="<?php echo e(route('faq')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">FAQ</a></li>
                            <li><a href="<?php echo e(route('terms')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Terms of Service</a></li>
                            <li><a href="<?php echo e(route('privacy')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Privacy Policy</a></li>
                            <li><a href="<?php echo e(route('refund')); ?>" class="text-slate-300 hover:text-white transition-colors duration-200">Refund Policy</a></li>
                        </ul>
                    </div>
                </div>

                
                <?php echo $__env->yieldPushContent('breadcrumb_schema'); ?>

                <div class="border-t border-slate-700 mt-12 pt-8">
                    <p class="text-slate-400 text-sm">&copy; <?php echo e(date('Y')); ?> EsureBet. All rights reserved. <span class="text-slate-500">|</span> <strong class="text-green-400">85%+ Accuracy</strong></p>
                    <p class="text-slate-500 text-xs mt-2">Disclaimer: Betting involves risk. Please gamble responsibly. 18+ only. Predictions are for informational purposes.</p>
                </div>
            </div>
        </footer>
    </div>

  
    <script src="<?php echo e(asset('js/app.js')); ?>?v=<?php echo e(md5_file(public_path('js/app.js'))); ?>" defer></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ duration: 800, easing: 'ease-in-out', once: true });
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/simeonuba/Downloads/public_html (1)/resources/views/layouts/app.blade.php ENDPATH**/ ?>