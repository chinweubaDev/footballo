<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/live-scores', [App\Http\Controllers\HomeController::class, 'liveScores'])->name('live-scores');
Route::post('/predict-match', [App\Http\Controllers\HomeController::class, 'predictMatch'])->name('predict.match');

// Auth routes
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->name('password.update');

// Predictions
Route::get('/predictions', [App\Http\Controllers\PredictionController::class, 'index'])->name('predictions');
Route::get('/predictions/category/{category}', [App\Http\Controllers\PredictionController::class, 'category'])->name('predictions.category');
Route::get('/predictions/premium', [App\Http\Controllers\PredictionController::class, 'premium'])->name('predictions.premium');
Route::get('/predictions/maxodds', [App\Http\Controllers\PredictionController::class, 'maxodds'])->name('predictions.maxodds');
Route::get('/predictions/over-1-5', [App\Http\Controllers\PredictionController::class, 'over15'])->name('predictions.over15');
Route::get('/predictions/over-2-5', [App\Http\Controllers\PredictionController::class, 'over25'])->name('predictions.over25');
Route::get('/predictions/double-chance', [App\Http\Controllers\PredictionController::class, 'doubleChance'])->name('predictions.double-chance');
Route::get('/predictions/bts', [App\Http\Controllers\PredictionController::class, 'bts'])->name('predictions.bts');
Route::get('/predictions/draw', [App\Http\Controllers\PredictionController::class, 'draw'])->name('predictions.draw');
Route::get('/predictions/tomorrow', [App\Http\Controllers\PredictionController::class, 'tomorrow'])->name('predictions.tomorrow');

// Tips
Route::get('/tips/vip', [App\Http\Controllers\TipsController::class, 'vip'])->name('tips.vip');
Route::get('/tips/vvip', [App\Http\Controllers\TipsController::class, 'vvip'])->name('tips.vvip');

// Matches
Route::get('/matches/standings', [App\Http\Controllers\MatchController::class, 'standings'])->name('match.standings');
Route::get('/matches/upcoming', [App\Http\Controllers\MatchController::class, 'upcoming'])->name('match.upcoming');
Route::get('/match/{id}', [App\Http\Controllers\MatchDetailController::class, 'show'])->name('match.detail');

// Static pages
Route::get('/pricing', [App\Http\Controllers\PricingController::class, 'index'])->name('pricing');
Route::view('/faq', 'faq')->name('faq');
Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/refund', 'refund')->name('refund');
Route::view('/support', 'support')->name('support');
Route::view('/contact', 'contact')->name('contact');

// User dashboard & profile
Route::get('/dashboard', [App\Http\Controllers\UserController::class, 'dashboard'])->name('dashboard');
Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');

// Payment
Route::get('/payment/methods', [App\Http\Controllers\PaymentController::class, 'showPaymentMethods'])->name('payment.methods');
Route::get('/payment/details/{plan}', [App\Http\Controllers\PaymentController::class, 'showPaymentDetails'])->name('payment.details');
Route::get('/payment/crypto', [App\Http\Controllers\PaymentController::class, 'cryptoPayment'])->name('payment.crypto');
Route::get('/payment/mock', [App\Http\Controllers\PaymentController::class, 'mockPayment'])->name('payment.mock');

// Basketball
Route::get('/basketball', [App\Http\Controllers\PredictionController::class, 'basketball'])->name('basketball');

// SEO
Route::get('/sitemap.xml', [App\Http\Controllers\HomeController::class, 'sitemap'])->name('sitemap');

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Fixtures
    Route::get('/fixtures', [AdminController::class, 'fixtures'])->name('fixtures');
    Route::get('/fixtures/create', [AdminController::class, 'createFixture'])->name('fixtures.create');
    Route::post('/fixtures', [AdminController::class, 'storeFixture'])->name('fixtures.store');
    Route::get('/fixtures/fetch', [AdminController::class, 'fetchFixtures'])->name('fixtures.fetch');
    Route::post('/fixtures/add', [AdminController::class, 'addFixture'])->name('fixtures.add');
    Route::get('/fixtures/{fixture}', [AdminController::class, 'getFixture'])->name('fixtures.get');
    Route::put('/fixtures/{fixture}', [AdminController::class, 'updateFixture'])->name('fixtures.update');
    Route::delete('/fixtures/{fixture}', [AdminController::class, 'deleteFixture'])->name('fixtures.delete');

    // Predictions
    Route::get('/predictions', [AdminController::class, 'predictions'])->name('predictions');
    Route::get('/predictions/create', [AdminController::class, 'createPrediction'])->name('predictions.create');
    Route::post('/predictions', [AdminController::class, 'storePrediction'])->name('predictions.store');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/deactivate', [AdminController::class, 'deactivateUser'])->name('users.deactivate');
    Route::post('/users/{user}/upgrade', [AdminController::class, 'upgradeUser'])->name('users.upgrade');
    Route::post('/users/{user}/downgrade', [AdminController::class, 'downgradeUser'])->name('users.downgrade');

    // Payments
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::post('/payments/{payment}/requery', [AdminController::class, 'requeryPayment'])->name('payments.requery');

    // Results
    Route::get('/results', [AdminController::class, 'results'])->name('results');
    Route::get('/results/create', [AdminController::class, 'createResult'])->name('results.create');
    Route::post('/results', [AdminController::class, 'storeResult'])->name('results.store');
    Route::get('/results/{result}/edit', [AdminController::class, 'editResult'])->name('results.edit');
    Route::put('/results/{result}', [AdminController::class, 'updateResult'])->name('results.update');
    Route::delete('/results/{result}', [AdminController::class, 'deleteResult'])->name('results.delete');

    // Subscriptions
    Route::get('/user-subscriptions', [AdminController::class, 'userSubscriptions'])->name('user-subscriptions');

    // Pricing
    Route::get('/pricing', [AdminController::class, 'pricing'])->name('pricing');
    Route::post('/pricing', [AdminController::class, 'storePricing'])->name('pricing.store');
    Route::put('/pricing/{plan}', [AdminController::class, 'updatePricing'])->name('pricing.update');
    Route::delete('/pricing/{plan}', [AdminController::class, 'deletePricing'])->name('pricing.delete');

    // Payment Methods
    Route::get('/payment-methods', [AdminController::class, 'paymentMethods'])->name('payment-methods');
    Route::post('/payment-methods', [AdminController::class, 'storePaymentMethod'])->name('payment-methods.store');
    Route::put('/payment-methods/{method}', [AdminController::class, 'updatePaymentMethod'])->name('payment-methods.update');
    Route::delete('/payment-methods/{method}', [AdminController::class, 'deletePaymentMethod'])->name('payment-methods.delete');

    // API helpers (JSON)
    Route::get('/api/countries', [AdminController::class, 'getCountries'])->name('countries');
    Route::get('/api/leagues', [AdminController::class, 'getLeagues'])->name('leagues');
});
