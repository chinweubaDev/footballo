<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\TipsController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/predictions', [PredictionController::class, 'index'])->name('predictions');
Route::get('/premium-tips', [PredictionController::class, 'premium'])->name('premium.tips');
Route::get('/maxodds-tips', [PredictionController::class, 'maxodds'])->name('maxodds.tips');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/pricing/country', [PricingController::class, 'getPricingByCountryAjax'])->name('pricing.country');

// Static pages
Route::view('/support', 'support')->name('support');
Route::view('/contact', 'contact')->name('contact');
Route::view('/faq', 'faq')->name('faq');
Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/refund', 'refund')->name('refund');

// Debug route for testing payment (remove in production)
Route::get('/debug/payment', function() {
    return response()->json([
        'auth_check' => auth()->check(),
        'user' => auth()->user(),
        'flutterwave_config' => [
            'public_key' => config('services.flutterwave.public_key'),
            'secret_key' => !empty(config('services.flutterwave.secret_key')),
        ]
    ]);
});

// Category routes
Route::get('/over-1-5', [PredictionController::class, 'over15'])->name('predictions.over15');
Route::get('/over-2-5', [PredictionController::class, 'over25'])->name('predictions.over25');
Route::get('/double-chance', [PredictionController::class, 'doubleChance'])->name('predictions.double-chance');
Route::get('/bts', [PredictionController::class, 'bts'])->name('predictions.bts');
Route::get('/draw', [PredictionController::class, 'draw'])->name('predictions.draw');

// Match routes
Route::get('/standings', [MatchController::class, 'standings'])->name('match.standings');
Route::get('/upcoming', [MatchController::class, 'upcoming'])->name('match.upcoming');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Payment routes
Route::get('/payment/methods', [PaymentController::class, 'showPaymentMethods'])->name('payment.methods');
Route::get('/payment/details', [PaymentController::class, 'showPaymentDetails'])->name('payment.details');
Route::post('/payment/initialize', [PaymentController::class, 'initialize'])->name('payment.initialize');
Route::match(['get', 'post'], '/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/payment/crypto/{transactionId}', [PaymentController::class, 'cryptoPayment'])->name('payment.crypto');
Route::get('/payment/mock/{tx_ref}', [PaymentController::class, 'mockPayment'])->name('payment.mock');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/fixtures', [AdminController::class, 'fixtures'])->name('fixtures');
    Route::get('/fixtures/{fixture}', [AdminController::class, 'getFixture'])->name('fixtures.get');
    Route::get('/countries', [AdminController::class, 'getCountries'])->name('countries');
    Route::get('/leagues', [AdminController::class, 'getLeagues'])->name('leagues');
    Route::post('/fixtures/fetch', [AdminController::class, 'fetchFixtures'])->name('fixtures.fetch');
    Route::post('/fixtures/add', [AdminController::class, 'addFixture'])->name('fixtures.add');
    Route::put('/fixtures/{fixture}', [AdminController::class, 'updateFixture'])->name('fixtures.update');
    Route::delete('/fixtures/{fixture}', [AdminController::class, 'deleteFixture'])->name('fixtures.delete');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::post('/payments/{payment}/requery', [AdminController::class, 'requeryPayment'])->name('payments.requery');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/upgrade', [AdminController::class, 'upgradeUser'])->name('users.upgrade');
    Route::post('/users/{user}/deactivate', [AdminController::class, 'deactivateUser'])->name('users.deactivate');
    
    // Pricing Management
    Route::get('/pricing', [AdminController::class, 'pricing'])->name('pricing');
    Route::post('/pricing', [AdminController::class, 'storePricing'])->name('pricing.store');
    Route::put('/pricing/{plan}', [AdminController::class, 'updatePricing'])->name('pricing.update');
    Route::delete('/pricing/{plan}', [AdminController::class, 'deletePricing'])->name('pricing.delete');
    
    // Payment Methods Management
    Route::get('/payment-methods', [AdminController::class, 'paymentMethods'])->name('payment-methods');
    Route::post('/payment-methods', [AdminController::class, 'storePaymentMethod'])->name('payment-methods.store');
    Route::put('/payment-methods/{method}', [AdminController::class, 'updatePaymentMethod'])->name('payment-methods.update');
    Route::delete('/payment-methods/{method}', [AdminController::class, 'deletePaymentMethod'])->name('payment-methods.delete');
    
    // User Subscriptions Management
    Route::get('/user-subscriptions', [AdminController::class, 'userSubscriptions'])->name('user-subscriptions');
    Route::post('/users/{user}/upgrade', [AdminController::class, 'upgradeUser'])->name('users.upgrade');
    Route::post('/users/{user}/downgrade', [AdminController::class, 'downgradeUser'])->name('users.downgrade');
    
    // Results Management
    Route::get('/results', [AdminController::class, 'results'])->name('results');
    Route::get('/results/create', [AdminController::class, 'createResult'])->name('results.create');
    Route::post('/results', [AdminController::class, 'storeResult'])->name('results.store');
    Route::get('/results/{result}/edit', [AdminController::class, 'editResult'])->name('results.edit');
    Route::put('/results/{result}', [AdminController::class, 'updateResult'])->name('results.update');
    Route::delete('/results/{result}', [AdminController::class, 'deleteResult'])->name('results.delete');
});

// Tips routes (accessible to all, but content restricted based on subscription)
Route::get('/tips/vip', [TipsController::class, 'vip'])->name('tips.vip');
Route::get('/tips/vvip', [TipsController::class, 'vvip'])->name('tips.vvip');
