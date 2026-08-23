<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PredictionAdminController;
use App\Http\Controllers\Admin\PredictionLeagueController;
use App\Http\Controllers\Admin\PredictionMarketController;
use App\Http\Controllers\Admin\PredictionPerformanceController;
use App\Http\Controllers\Admin\PredictionModelController;
use App\Http\Controllers\Admin\PredictionGateController;
use App\Http\Controllers\Admin\BacktestController;
use App\Http\Controllers\Admin\ValidationMatrixController;
use App\Http\Controllers\Admin\LeagueMarketGateController;
use App\Http\Controllers\Admin\LiveValidationController;
use App\Http\Controllers\Admin\SystemController;

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
Route::get('/predictions/1x2', [App\Http\Controllers\PredictionController::class, 'oneXTwo'])->name('predictions.1x2');
Route::get('/predictions/tomorrow', [App\Http\Controllers\PredictionController::class, 'tomorrow'])->name('predictions.tomorrow');
Route::get('/predictions/correct-score', [App\Http\Controllers\PredictionController::class, 'correctScore'])->name('predictions.correct-score');
Route::get('/predictions/{league:slug}', [App\Http\Controllers\PredictionController::class, 'league'])->name('predictions.league');
Route::get('/predictions/{league:slug}/{fixture:slug}', [App\Http\Controllers\PredictionController::class, 'fixture'])->name('predictions.fixture');

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
Route::post('/payment/initialize', [App\Http\Controllers\PaymentController::class, 'initialize'])->name('payment.initialize');
Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');

// Basketball
Route::get('/basketball', [App\Http\Controllers\PredictionController::class, 'basketball'])->name('basketball');

// Blog
Route::get('/blog', [App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category}', [App\Http\Controllers\BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{tag}', [App\Http\Controllers\BlogController::class, 'byTag'])->name('blog.tag');
Route::get('/blog/{slug}', [App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// SEO
Route::get('/sitemap.xml', [App\Http\Controllers\HomeController::class, 'sitemap'])->name('sitemap');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
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

    // Prediction Control Center
    Route::get('/predictions', [PredictionAdminController::class, 'dashboard'])->name('predictions');
    Route::get('/predictions/live', [PredictionAdminController::class, 'live'])->name('predictions.live');
    Route::get('/predictions/list', [PredictionAdminController::class, 'index'])->name('predictions.list');
    Route::get('/predictions/leagues', [PredictionLeagueController::class, 'index'])->name('predictions.leagues');
    Route::get('/predictions/leagues/discover', [PredictionLeagueController::class, 'discover'])->name('predictions.leagues.discover');
    Route::post('/predictions/leagues/import', [PredictionLeagueController::class, 'import'])->name('predictions.leagues.import');
    Route::post('/predictions/leagues/{league}/toggle', [PredictionLeagueController::class, 'toggleEnabled'])->name('predictions.leagues.toggle');
    Route::post('/predictions/leagues/{league}/settings', [PredictionLeagueController::class, 'updateSettings'])->name('predictions.leagues.settings');
    Route::get('/predictions/markets', [PredictionMarketController::class, 'index'])->name('predictions.markets');
    Route::post('/predictions/markets/{category}/toggle', [PredictionMarketController::class, 'toggleEnabled'])->name('predictions.markets.toggle');
    Route::post('/predictions/markets/{category}/settings', [PredictionMarketController::class, 'updateSettings'])->name('predictions.markets.settings');

    // Model performance dashboard
    Route::get('/predictions/performance', [PredictionPerformanceController::class, 'index'])->name('predictions.performance');

    // Phase 1M — live validation, model comparison & evidence analysis
    Route::get('/predictions/live-validation', [LiveValidationController::class, 'summary'])->name('predictions.live-validation');
    Route::get('/predictions/live-validation/report', [LiveValidationController::class, 'report'])->name('predictions.live-validation.report');
    Route::get('/predictions/performance/markets', [LiveValidationController::class, 'markets'])->name('predictions.performance.markets');
    Route::get('/predictions/performance/leagues', [LiveValidationController::class, 'leagues'])->name('predictions.performance.leagues');
    Route::get('/predictions/performance/matrix', [LiveValidationController::class, 'matrix'])->name('predictions.performance.matrix');
    Route::get('/predictions/performance/export', [LiveValidationController::class, 'export'])->name('predictions.performance.export');

    // Model versions & comparison
    Route::get('/predictions/models', [PredictionModelController::class, 'index'])->name('predictions.models');
    Route::get('/predictions/models/compare', [PredictionModelController::class, 'compare'])->name('predictions.models.compare');
    Route::get('/predictions/models/data-quality', [PredictionModelController::class, 'dataQuality'])->name('predictions.models.data-quality');
    Route::post('/predictions/models/{model}/approve', [PredictionModelController::class, 'approve'])->name('predictions.models.approve');
    Route::post('/predictions/models/{model}/reject', [PredictionModelController::class, 'reject'])->name('predictions.models.reject');
    Route::post('/predictions/models/{model}/activate', [PredictionModelController::class, 'activate'])->name('predictions.models.activate');
    Route::post('/predictions/models/{model}/retire', [PredictionModelController::class, 'retire'])->name('predictions.models.retire');
    Route::post('/predictions/models/{model}/rollback', [PredictionModelController::class, 'rollback'])->name('predictions.models.rollback');

    // Publication gate optimization (Phase 1G.1)
    Route::get('/predictions/gates', [PredictionGateController::class, 'index'])->name('predictions.gates');
    Route::post('/predictions/gates/{category}/approve', [PredictionGateController::class, 'approve'])->name('predictions.gates.approve');
    Route::post('/predictions/gates/{category}/reject', [PredictionGateController::class, 'reject'])->name('predictions.gates.reject');

    // League x market publication gate matrix (Phase 1I)
    Route::get('/predictions/settings/matrix', [LeagueMarketGateController::class, 'index'])->name('predictions.settings.matrix');
    Route::post('/predictions/settings/matrix/{league}/{marketCode}', [LeagueMarketGateController::class, 'update'])->name('predictions.settings.matrix.update');

    // Shadow mode & multi-league validation
    Route::get('/predictions/shadow', [PredictionModelController::class, 'shadow'])->name('predictions.shadow');
    Route::get('/predictions/validation', [PredictionModelController::class, 'validation'])->name('predictions.validation');

    // Phase 1G.2 full League x Market x Model matrix
    Route::get('/predictions/validation/matrix', [ValidationMatrixController::class, 'matrix'])->name('predictions.validation.matrix');
    Route::get('/predictions/validation/ranking', [ValidationMatrixController::class, 'ranking'])->name('predictions.validation.ranking');
    Route::get('/predictions/validation/candidates', [ValidationMatrixController::class, 'candidates'])->name('predictions.validation.candidates');
    Route::post('/predictions/validation/candidates/decide', [ValidationMatrixController::class, 'decide'])->name('predictions.validation.candidates.decide');

    // Phase 1P multi-season validation
    Route::get('/predictions/validation/multi-season', [ValidationMatrixController::class, 'multiSeason'])->name('predictions.validation.multi-season');

    // Historical backtesting
    Route::get('/predictions/backtesting', [BacktestController::class, 'index'])->name('predictions.backtesting.index');
    Route::get('/predictions/backtesting/create', [BacktestController::class, 'create'])->name('predictions.backtesting.create');
    Route::post('/predictions/backtesting', [BacktestController::class, 'store'])->name('predictions.backtesting.store');
    Route::get('/predictions/backtesting/{backtest}', [BacktestController::class, 'show'])->name('predictions.backtesting.show');
    Route::post('/predictions/backtesting/{backtest}/cancel', [BacktestController::class, 'cancel'])->name('predictions.backtesting.cancel');
    Route::post('/predictions/backtesting/{backtest}/archive', [BacktestController::class, 'archive'])->name('predictions.backtesting.archive');
    Route::get('/predictions/backtesting/{backtest}/export', [BacktestController::class, 'export'])->name('predictions.backtesting.export');

    Route::get('/predictions/{prediction}', [PredictionAdminController::class, 'show'])->name('predictions.show');
    Route::post('/predictions/{prediction}/override', [PredictionAdminController::class, 'override'])->name('predictions.override');
    Route::post('/predictions/{prediction}/revert', [PredictionAdminController::class, 'revert'])->name('predictions.revert');
    Route::post('/predictions/{prediction}/lock', [PredictionAdminController::class, 'lock'])->name('predictions.lock');
    Route::post('/predictions/{prediction}/unlock', [PredictionAdminController::class, 'unlock'])->name('predictions.unlock');
    Route::post('/predictions/{prediction}/publish', [PredictionAdminController::class, 'publish'])->name('predictions.publish');
    Route::post('/predictions/{prediction}/unpublish', [PredictionAdminController::class, 'unpublish'])->name('predictions.unpublish');
    Route::post('/predictions/{prediction}/feature', [PredictionAdminController::class, 'feature'])->name('predictions.feature');
    Route::post('/predictions/{prediction}/unfeature', [PredictionAdminController::class, 'unfeature'])->name('predictions.unfeature');

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

    // Blog
    Route::get('/blog', [App\Http\Controllers\Admin\AdminBlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/create', [App\Http\Controllers\Admin\AdminBlogController::class, 'create'])->name('blog.create');
    Route::post('/blog', [App\Http\Controllers\Admin\AdminBlogController::class, 'store'])->name('blog.store');
    Route::get('/blog/{post}/edit', [App\Http\Controllers\Admin\AdminBlogController::class, 'edit'])->name('blog.edit');
    Route::put('/blog/{post}', [App\Http\Controllers\Admin\AdminBlogController::class, 'update'])->name('blog.update');
    Route::delete('/blog/{post}', [App\Http\Controllers\Admin\AdminBlogController::class, 'destroy'])->name('blog.destroy');
    Route::post('/blog/{post}/toggle', [App\Http\Controllers\Admin\AdminBlogController::class, 'toggleStatus'])->name('blog.toggle');

    // Payment Methods
    Route::get('/payment-methods', [AdminController::class, 'paymentMethods'])->name('payment-methods');
    Route::post('/payment-methods', [AdminController::class, 'storePaymentMethod'])->name('payment-methods.store');
    Route::put('/payment-methods/{method}', [AdminController::class, 'updatePaymentMethod'])->name('payment-methods.update');
    Route::delete('/payment-methods/{method}', [AdminController::class, 'deletePaymentMethod'])->name('payment-methods.delete');

    // API helpers (JSON)
    Route::get('/api/countries', [AdminController::class, 'getCountries'])->name('countries');
    Route::get('/api/leagues', [AdminController::class, 'getLeagues'])->name('leagues');

    // System monitoring (Phase 1K)
    Route::get('/system/api', [SystemController::class, 'api'])->name('system.api');
    Route::get('/system/alerts', [SystemController::class, 'alerts'])->name('system.alerts');
    Route::get('/system/pipeline', [SystemController::class, 'pipeline'])->name('system.pipeline');
    Route::get('/system/queue', [SystemController::class, 'queue'])->name('system.queue');
    Route::post('/system/queue/{id}/retry', [SystemController::class, 'retryFailedJob'])->name('system.queue.retry');
    Route::post('/system/queue/{id}/forget', [SystemController::class, 'forgetFailedJob'])->name('system.queue.forget');
});
