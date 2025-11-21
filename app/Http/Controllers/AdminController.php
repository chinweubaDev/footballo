<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PricingPlan;
use App\Models\Prediction;
use App\Models\Result;
use App\Models\Subscription;
use App\Models\Tip;
use App\Models\User;
use App\Services\ApiFootballService;
use App\Services\FlutterwaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware is now handled in routes/web.php
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'premium_users' => User::where('is_premium', true)->count(),
            'total_payments' => Payment::where('status', 'completed')->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'total_fixtures' => Fixture::count(),
            'today_tips' => Fixture::where('today_tip', true)->count(),
            'featured_predictions' => Fixture::where('featured', true)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function fixtures()
    {
        $fixtures = Fixture::orderBy('match_date', 'desc')->paginate(20);
        return view('admin.fixtures', compact('fixtures'));
    }

    public function createFixture()
    {
        return view('admin.create-fixture');
    }

    public function storeFixture(Request $request)
    {
        $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'league' => 'required|string|max:255',
            'match_date' => 'required|date',
            'match_time' => 'required',
        ]);

        Fixture::create($request->all());

        return redirect()->route('admin.fixtures')->with('success', 'Fixture created successfully!');
    }

    public function predictions()
    {
        $predictions = Prediction::with('fixture')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.predictions', compact('predictions'));
    }

    public function createPrediction()
    {
        $fixtures = Fixture::upcoming()->orderBy('match_date')->get();
        return view('admin.create-prediction', compact('fixtures'));
    }

    public function storePrediction(Request $request)
    {
        $request->validate([
            'fixture_id' => 'required|exists:fixtures,id',
            'type' => 'required|in:tipsoftheday,featured,vip',
            'category' => 'required|string',
            'prediction' => 'required|string',
            'odds' => 'required|numeric|min:0',
            'probability' => 'required|integer|min:0|max:100',
            'tip' => 'nullable|string',
            'is_vip' => 'boolean',
        ]);

        Prediction::create($request->all());

        return redirect()->route('admin.predictions')->with('success', 'Prediction created successfully!');
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function getCountries()
    {
        try {
            // Check if API key is set
            $apiKey = config('services.api_football.key');
            if (empty($apiKey)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'API Football key is not configured. Please set API_FOOTBALL_KEY in your .env file.'
                ], 400);
            }

            $apiFootballService = new ApiFootballService();
            $response = $apiFootballService->getCountries();

            Log::info('API Football Countries Response', [
                'response' => $response,
                'api_key_set' => !empty($apiKey),
                'api_key_length' => strlen($apiKey),
                'base_url' => config('services.api_football.base_url')
            ]);

            if ($response && isset($response['response'])) {
                return response()->json([
                    'status' => 'success',
                    'countries' => $response['response']
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch countries from API',
                'debug' => $response
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error in getCountries', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Exception occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLeagues(Request $request)
    {
        $request->validate([
            'country' => 'required|string',
        ]);

        $apiFootballService = new ApiFootballService();
        $response = $apiFootballService->getLeaguesByCountry($request->country);

        if ($response && isset($response['response'])) {
            return response()->json([
                'status' => 'success',
                'leagues' => $response['response']
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch leagues from API'
        ], 400);
    }

    public function fetchFixtures(Request $request)
    {
        try {
            $request->validate([
                'country' => 'required|string',
                'league' => 'required|integer',
                'season' => 'required|integer',
                'date' => 'required|date',
            ]);

            $apiFootballService = new ApiFootballService();
            $response = $apiFootballService->getFixtures(
                $request->league,
                $request->season,
                $request->date
            );

            if ($response && isset($response['response'])) {
                return response()->json([
                    'status' => 'success',
                    'fixtures' => $response['response']
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch fixtures from API'
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Exception in fetchFixtures', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Exception occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addFixture(Request $request)
    {
        //dd( $request);
        $request->validate([
            'api_fixture_id' => 'required|integer|unique:fixtures',
            'league_name' => 'required|string',
            'league_country' => 'required|string',
            'league_logo' => 'required|string',
            'league_flag' => 'required|string',
            'league_id' => 'required|integer',
            'season' => 'required|integer',
            'home_team' => 'required|string',
            'away_team' => 'required|string',
            'home_team_id' => 'required|integer',
            'away_team_id' => 'required|integer',
            'home_team_logo'  => 'required|string',
            'away_team_logo'  => 'required|string',
            'match_date' => 'required|date',
            'category' => 'required|string',
            'tip' => 'required|string',
            'confidence' => 'required|integer|min:1|max:100',
            'odds' => 'nullable|numeric',
            'analysis' => 'nullable|string',
            'today_tip' => 'boolean',
            'featured' => 'boolean',
            'maxodds_tip' => 'boolean',
            'is_premium' => 'boolean',
            'is_vip' => 'boolean',
            'is_vvip' => 'boolean',
            'is_surepick' => 'boolean',
            // Individual tip content fields
            'today_tip_content' => 'nullable|string',
            'featured_tip_content' => 'nullable|string',
            'vip_tip_content' => 'nullable|string',
            'vvip_tip_content' => 'nullable|string',
            'surepick_tip_content' => 'nullable|string',
            'maxodds_tip_content' => 'nullable|string',
            // VIP/VVIP Tips validation
            'tip_type' => 'nullable|in:vip,vvip',
            'tip_title' => 'nullable|string|max:255',
            'tip_content' => 'nullable|string',
            'match_date' => 'nullable|date',
            'match_time' => 'nullable|date_format:H:i',
            'is_featured_tip' => 'boolean',
        ]);

        // Create fixture
        $fixture = Fixture::create([
            'api_fixture_id' => $request->api_fixture_id,
            'league_name' => $request->league_name,
            'league_country' => $request->league_country,
            'league_id' => $request->league_id,
            'league_logo'  => $request->league_logo,
            'league_flag'  => $request->league_flag,
            'season' => $request->season,
            'home_team' => $request->home_team,
            'away_team' => $request->away_team,
            'home_team_logo'  => $request->home_team_logo,
            'away_team_logo'  => $request->away_team_logo,
            'home_team_id' => $request->home_team_id,
            'away_team_id' => $request->away_team_id,
            'match_date' => $request->match_date,
            'today_tip' => $request->boolean('today_tip'),
            'featured' => $request->boolean('featured'),
            'maxodds_tip' => $request->boolean('maxodds_tip'),
            'is_vip' => $request->boolean('is_vip'),
            'is_vvip' => $request->boolean('is_vvip'),
            'is_surepick' => $request->boolean('is_surepick'),
        ]);

        // Create prediction
        Prediction::create([
            'fixture_id' => $fixture->id,
            'category' => $request->category,
            'tip' => $request->tip,
            'confidence' => $request->confidence,
            'odds' => $request->odds,
            'analysis' => $request->analysis,
            'is_premium' => $request->boolean('is_premium'),
            'is_maxodds' => $request->boolean('maxodds_tip'),
        ]);

        // Automatically create individual tip entries for each checked category
        $categories = [];
        
        if ($request->boolean('today_tip')) {
            $categories[] = [
                'key' => 'today_tip',
                'content' => $request->today_tip_content
            ];
        }
        
        if ($request->boolean('featured')) {
            $categories[] = [
                'key' => 'featured',
                'content' => $request->featured_tip_content
            ];
        }
        
        if ($request->boolean('is_vip')) {
            $categories[] = [
                'key' => 'vip',
                'content' => $request->vip_tip_content
            ];
        }
        
        if ($request->boolean('is_vvip')) {
            $categories[] = [
                'key' => 'vvip',
                'content' => $request->vvip_tip_content
            ];
        }
        
        if ($request->boolean('is_surepick')) {
            $categories[] = [
                'key' => 'surepick',
                'content' => $request->surepick_tip_content
            ];
        }

        if ($request->boolean('maxodds_tip')) {
            $categories[] = [
                'key' => 'maxodds',
                'content' => $request->maxodds_tip_content
            ];
        }

        // Create a tip entry for each category
        foreach ($categories as $categoryData) {
            $category = $categoryData['key'];
            $customContent = $categoryData['content'];
            
            $tipType = in_array($category, ['vip', 'vvip']) ? $category : 'vip';
            
            $title = $this->generateTipTitle($category, $request->home_team, $request->away_team);
            
            // Use custom content if provided, otherwise generate default content
            $content = !empty($customContent) 
                ? $customContent 
                : $this->generateTipContent($request, $category);
            
            Tip::create([
                'title' => $title,
                'content' => $content,
                'type' => $tipType,
                'category' => $category,
                'fixture_id' => $fixture->api_fixture_id,
                'league_name' => $request->league_name,
                'home_team' => $request->home_team,
                'away_team' => $request->away_team,
                'match_date' => $request->match_date,
                'match_time' => $request->match_time ?? date('H:i', strtotime($request->match_date)),
                'prediction' => $request->tip,
                'odds' => $request->odds,
                'status' => 'pending',
                'is_featured' => $category === 'featured',
                'is_active' => true,
            ]);
        }

        // Legacy: Create VIP/VVIP tip if specified with old method
        if ($request->filled('tip_type') && $request->filled('tip_title') && $request->filled('tip_content')) {
            Tip::create([
                'title' => $request->tip_title,
                'content' => $request->tip_content,
                'type' => $request->tip_type,
                'category' => $request->tip_type,
                'fixture_id' => $fixture->api_fixture_id,
                'league_name' => $request->league_name,
                'home_team' => $request->home_team,
                'away_team' => $request->away_team,
                'match_date' => $request->match_date,
                'match_time' => $request->match_time,
                'prediction' => $request->tip,
                'odds' => $request->odds,
                'status' => 'pending',
                'is_featured' => $request->boolean('is_featured_tip'),
                'is_active' => true,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Fixture and prediction added successfully' . (count($categories) > 0 ? ' with ' . count($categories) . ' tip(s)' : '')
        ]);
    }

    /**
     * Generate tip title based on category
     */
    private function generateTipTitle($category, $homeTeam, $awayTeam)
    {
        $categoryLabels = [
            'today_tip' => "Today's Tip",
            'featured' => 'Featured Match',
            'vip' => 'VIP Tip',
            'vvip' => 'VVIP Exclusive',
            'surepick' => 'Sure Pick',
            'maxodds' => 'MaxOdds Tip',
        ];

        $label = $categoryLabels[$category] ?? 'Tip';
        return "{$label}: {$homeTeam} vs {$awayTeam}";
    }

    /**
     * Generate tip content based on request data and category
     */
    private function generateTipContent($request, $category)
    {
        $confidence = $request->confidence;
        $analysis = $request->analysis ?? 'Analysis not provided';
        
        $content = "**Match Preview**\n\n";
        $content .= "{$request->home_team} will face {$request->away_team} in the {$request->league_name}.\n\n";
        $content .= "**Our Prediction:** {$request->tip}\n\n";
        $content .= "**Confidence Level:** {$confidence}%\n\n";
        
        if ($request->odds) {
            $content .= "**Odds:** {$request->odds}\n\n";
        }
        
        $content .= "**Analysis:**\n{$analysis}";
        
        return $content;
    }

    public function getFixture(Fixture $fixture)
    {
        return response()->json([
            'status' => 'success',
            'fixture' => $fixture
        ]);
    }

    public function updateFixture(Request $request, Fixture $fixture)
    {
        $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'league_name' => 'required|string|max:255',
            'match_date' => 'required|date',
            'today_tip' => 'boolean',
            'featured' => 'boolean',
            'maxodds_tip' => 'boolean',
            'is_vip' => 'boolean',
            'is_vvip' => 'boolean',
            'is_surepick' => 'boolean',
        ]);

        $fixture->update([
            'home_team' => $request->home_team,
            'away_team' => $request->away_team,
            'league_name' => $request->league_name,
            'match_date' => $request->match_date,
            'today_tip' => $request->boolean('today_tip'),
            'featured' => $request->boolean('featured'),
            'maxodds_tip' => $request->boolean('maxodds_tip'),
            'is_vip' => $request->boolean('is_vip'),
            'is_vvip' => $request->boolean('is_vvip'),
            'is_surepick' => $request->boolean('is_surepick'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Fixture updated successfully'
        ]);
    }

    public function deleteFixture(Fixture $fixture)
    {
        $fixture->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Fixture deleted successfully'
        ]);
    }

    public function payments()
    {
        $payments = Payment::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.payments', compact('payments'));
    }

    public function requeryPayment(Payment $payment, FlutterwaveService $flutterwaveService)
    {
        try {
            Log::info('Admin requery payment initiated', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'reference' => $payment->flutterwave_reference
            ]);

            // Only requery if payment is pending or failed
            if (!in_array($payment->status, ['pending', 'failed'])) {
                return back()->with('error', 'Only pending or failed payments can be requeried. Current status: ' . $payment->status);
            }

            // Verify payment with Flutterwave
            $verification = $flutterwaveService->verifyPayment($payment->flutterwave_reference);

            Log::info('Flutterwave requery response', [
                'payment_id' => $payment->id,
                'verification' => $verification
            ]);

            if ($verification['status'] === 'success' && isset($verification['data'])) {
                $txData = $verification['data'];
                
                // Check if payment was successful
                if ($txData['status'] === 'successful' && $txData['amount'] == $payment->amount && $txData['currency'] == $payment->currency) {
                    
                    // Update payment status
                    $payment->update([
                        'status' => 'completed',
                        'flutterwave_response' => json_encode($txData)
                    ]);

                    // Get pricing plan details
                    $pricingPlan = PricingPlan::where('key', $payment->plan_type)->first();
                    
                    // Create subscription if it doesn't exist
                    $subscription = Subscription::where('payment_id', $payment->id)->first();
                    if (!$subscription) {
                        Subscription::create([
                            'user_id' => $payment->user_id,
                            'payment_id' => $payment->id,
                            'plan_type' => $payment->plan_type,
                            'starts_at' => now(),
                            'expires_at' => $payment->expires_at,
                            'is_active' => true,
                        ]);
                    }

                    // Update user premium status
                    $user = $payment->user;
                    
                    // Determine subscription type and update accordingly
                    if ($pricingPlan && str_starts_with($pricingPlan->key, 'vvip_')) {
                        $user->update([
                            'is_premium' => true,
                            'premium_expires_at' => $payment->expires_at,
                            'subscription_type' => 'vvip',
                            'is_vvip_active' => true,
                            'vvip_expires_at' => $payment->expires_at,
                        ]);
                    } elseif ($pricingPlan && str_starts_with($pricingPlan->key, 'vip_')) {
                        $user->update([
                            'is_premium' => true,
                            'premium_expires_at' => $payment->expires_at,
                            'subscription_type' => 'vip',
                            'is_vip_active' => true,
                            'vip_expires_at' => $payment->expires_at,
                        ]);
                    } else {
                        $user->update([
                            'is_premium' => true,
                            'premium_expires_at' => $payment->expires_at,
                        ]);
                    }

                    // Send email notification
                    try {
                        $user->notify(new \App\Notifications\PaymentNotification($payment));
                    } catch (\Exception $e) {
                        Log::error('Failed to send payment notification after requery', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    Log::info('Payment requery successful - user upgraded', [
                        'payment_id' => $payment->id,
                        'user_id' => $user->id,
                        'subscription_type' => $user->subscription_type
                    ]);

                    return back()->with('success', 'Payment verified successfully! User has been upgraded to ' . strtoupper($user->subscription_type ?? 'PREMIUM'));
                } else {
                    // Payment not successful or amount mismatch
                    $status = $txData['status'] ?? 'unknown';
                    
                    if ($status === 'failed') {
                        $payment->update(['status' => 'failed']);
                    } elseif ($status === 'cancelled') {
                        $payment->update(['status' => 'cancelled']);
                    }

                    Log::warning('Payment requery - payment not successful', [
                        'payment_id' => $payment->id,
                        'flutterwave_status' => $status,
                        'expected_amount' => $payment->amount,
                        'received_amount' => $txData['amount'] ?? null
                    ]);

                    return back()->with('error', 'Payment verification failed. Status: ' . $status);
                }
            } else {
                Log::error('Payment requery failed', [
                    'payment_id' => $payment->id,
                    'error' => $verification['message'] ?? 'Unknown error'
                ]);

                return back()->with('error', 'Failed to verify payment: ' . ($verification['message'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error('Exception during payment requery', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while requerying payment: ' . $e->getMessage());
        }
    }


    public function deactivateUser(User $user)
    {
        // Prevent deactivating admin users
        if ($user->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot deactivate admin users'
            ], 403);
        }

        $user->update([
            'is_premium' => false,
            'premium_expires_at' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User deactivated successfully'
        ]);
    }

    // Pricing Management
    public function pricing()
    {
        $plans = PricingPlan::orderBy('sort_order')->get();
        return view('admin.pricing', compact('plans'));
    }

    public function storePricing(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:pricing_plans,key',
            'price_usd' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'sort_order' => 'required|integer|min:0'
        ]);

        $plan = PricingPlan::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Pricing plan created successfully',
            'plan' => $plan
        ]);
    }

    public function updatePricing(Request $request, PricingPlan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:pricing_plans,key,' . $plan->id,
            'price_usd' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'sort_order' => 'required|integer|min:0'
        ]);

        $plan->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Pricing plan updated successfully',
            'plan' => $plan
        ]);
    }

    public function deletePricing(PricingPlan $plan)
    {
        $plan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pricing plan deleted successfully'
        ]);
    }

    // Payment Methods Management
    public function paymentMethods()
    {
        $methods = PaymentMethod::orderBy('sort_order')->get();
        return view('admin.payment-methods', compact('methods'));
    }

    public function storePaymentMethod(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'crypto_type' => 'nullable|string|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'config' => 'nullable|array',
            'sort_order' => 'required|integer|min:0'
        ]);

        $method = PaymentMethod::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Payment method created successfully',
            'method' => $method
        ]);
    }

    public function updatePaymentMethod(Request $request, PaymentMethod $method)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'crypto_type' => 'nullable|string|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'config' => 'nullable|array',
            'sort_order' => 'required|integer|min:0'
        ]);

        $method->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Payment method updated successfully',
            'method' => $method
        ]);
    }

    public function deletePaymentMethod(PaymentMethod $method)
    {
        $method->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment method deleted successfully'
        ]);
    }

    public function userSubscriptions()
    {
        $users = User::with(['payments', 'subscriptions'])
            ->whereIn('subscription_type', ['vip', 'vvip'])
            ->orWhere('is_vip_active', true)
            ->orWhere('is_vvip_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.user-subscriptions', compact('users'));
    }

    public function upgradeUser(Request $request, User $user)
    {
        $request->validate([
            'subscription_type' => 'required|in:vip,vvip',
            'duration_days' => 'required|integer|min:1|max:365'
        ]);

        $durationDays = $request->duration_days;
        
        if ($request->subscription_type === 'vip') {
            $user->upgradeToVIP($durationDays);
            $message = "User upgraded to VIP for {$durationDays} days.";
        } else {
            $user->upgradeToVVIP($durationDays);
            $message = "User upgraded to VVIP for {$durationDays} days.";
        }

        return redirect()->back()->with('success', $message);
    }

    public function downgradeUser(User $user)
    {
        $user->downgradeToFree();
        return redirect()->back()->with('success', 'User downgraded to free plan.');
    }

    // Results Management
    public function results()
    {
        $results = Result::orderBy('date', 'desc')->paginate(20);
        return view('admin.results', compact('results'));
    }

    public function createResult()
    {
        return view('admin.create-result');
    }

    public function storeResult(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'odds' => 'required|numeric|min:1',
            'status' => 'required|in:win,lose',
            'type' => 'required|in:vip,vvip',
        ]);

        Result::create($request->all());

        return redirect()->route('admin.results')->with('success', 'Result added successfully!');
    }

    public function editResult(Result $result)
    {
        return view('admin.edit-result', compact('result'));
    }

    public function updateResult(Request $request, Result $result)
    {
        $request->validate([
            'date' => 'required|date',
            'odds' => 'required|numeric|min:1',
            'status' => 'required|in:win,lose',
            'type' => 'required|in:vip,vvip',
        ]);

        $result->update($request->all());

        return redirect()->route('admin.results')->with('success', 'Result updated successfully!');
    }

    public function deleteResult(Result $result)
    {
        $result->delete();
        return redirect()->route('admin.results')->with('success', 'Result deleted successfully!');
    }
}
