<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Services\FlutterwaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function showPaymentMethods(Request $request)
    {
        // Validate required parameters
        $request->validate([
            'plan' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
        ]);

        // Get the pricing plan details from database
        $pricingPlan = PricingPlan::where('key', $request->plan)
            ->where('is_active', true)
            ->first();

        if (!$pricingPlan) {
            return redirect()->route('pricing')->with('error', 'Selected plan not found or is no longer available.');
        }

        // Get active payment methods from database
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('payment.methods', [
            'plan' => $request->plan,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'pricingPlan' => $pricingPlan,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function showPaymentDetails(Request $request)
    {
        // Validate required parameters
        $request->validate([
            'plan' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'payment_method' => 'required|string',
            'crypto_type' => 'nullable|string',
        ]);

        // Get the pricing plan details from database
        $pricingPlan = PricingPlan::where('key', $request->plan)
            ->where('is_active', true)
            ->first();

        if (!$pricingPlan) {
            return redirect()->route('pricing')->with('error', 'Selected plan not found.');
        }

        // Get the payment method details from database
        $paymentMethod = PaymentMethod::where('type', $request->payment_method)
            ->when($request->crypto_type, function($query, $cryptoType) {
                return $query->where('crypto_type', $cryptoType);
            })
            ->where('is_active', true)
            ->first();

        if (!$paymentMethod) {
            return redirect()->route('payment.methods', [
                'plan' => $request->plan,
                'amount' => $request->amount,
                'currency' => $request->currency
            ])->with('error', 'Selected payment method not found.');
        }

        return view('payment.details', [
            'plan' => $request->plan,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'pricingPlan' => $pricingPlan,
            'paymentMethod' => $paymentMethod,
        ]);
    }

    public function showPayment($plan)
    {
        $plans = [
            '3_days' => [
                'name' => '3 Days',
                'price' => 5.99,
                'duration' => '3 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support']
            ],
            '1_week' => [
                'name' => '1 Week',
                'price' => 12.99,
                'duration' => '7 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates']
            ],
            '3_weeks' => [
                'name' => '3 Weeks',
                'price' => 29.99,
                'duration' => '21 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access']
            ],
            '1_month' => [
                'name' => '1 Month',
                'price' => 39.99,
                'duration' => '30 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access', 'Custom Alerts']
            ],
            '1_year' => [
                'name' => '1 Year',
                'price' => 299.99,
                'duration' => '365 days',
                'features' => ['VIP Tips', 'Expert Analysis', 'Premium Support', 'Live Updates', 'Priority Access', 'Custom Alerts', 'Personal Consultant']
            ]
        ];

        if (!isset($plans[$plan])) {
            return redirect()->route('pricing')->with('error', 'Invalid plan selected.');
        }

        $selectedPlan = $plans[$plan];
        $selectedPlan['key'] = $plan;

        return view('payment', compact('selectedPlan'));
    }

    public function initialize(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to continue with payment.');
        }

        $request->validate([
            'plan_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'payment_method' => 'required|in:flutterwave,paypal,skrill,crypto',
            'crypto_type' => 'nullable|in:BTC,USDT,ETH,BNB,TRX',
        ]);

        $user = Auth::user();
        
        // Get the pricing plan from database
        $pricingPlan = PricingPlan::where('key', $request->plan_type)
            ->where('is_active', true)
            ->first();
            
        if (!$pricingPlan) {
            return redirect()->back()->with('error', 'Selected plan not found or is no longer available.');
        }
        
        $transactionId = 'TXN_Esure' . Str::random(10);
        
        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'transaction_id' => $transactionId,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'plan_type' => $request->plan_type,
            'payment_method' => $request->payment_method,
            'crypto_type' => $request->crypto_type,
            'plan_duration_days' => $pricingPlan->duration_days,
            'expires_at' => now()->addDays($pricingPlan->duration_days),
            'status' => 'pending',
        ]);

        try {
            // Initialize payment based on method
            $checkoutUrl = $this->initializePayment($payment, $request->payment_method, $request->crypto_type);

            return redirect($checkoutUrl);
        } catch (\Exception $e) {
            Log::error('Payment initialization failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Payment initialization failed: ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $transactionId = $request->input('tx_ref');
        $status = $request->input('status');
        $txid = $request->input('transaction_id');
        
        Log::info('Payment callback received', [
            'tx_ref' => $transactionId,
            'status' => $status,
            'all_params' => $request->all()
        ]);
        $payment = Payment::where('transaction_id', $transactionId)->first();
        
         $payment->update([
            'flutterwave_reference' => $txid,
        ]);
     
        if (!$payment) {
            Log::error('Payment not found in callback', ['tx_ref' => $transactionId]);
            return redirect()->route('pricing')->with('error', 'Payment not found');
        }

        // Verify payment with Flutterwave
        if ($payment->payment_method === 'flutterwave' && $status === 'successful') {
            $flutterwaveService = new FlutterwaveService();
            $verification = $flutterwaveService->verifyPayment($txid);
            
            Log::info('Flutterwave verification result', [
                'tx_ref' => $transactionId,
                'verification_status' => $verification['status'] ?? 'unknown',
                'verification_data_status' => $verification['data']['status'] ?? 'unknown',
                'expected_amount' => $payment->amount,
                'verified_amount' => $verification['data']['amount'] ?? 0,
                'expected_currency' => $payment->currency,
                'verified_currency' => $verification['data']['currency'] ?? 'unknown',
            ]);
            
            if ($verification['status'] === 'success' && 
                isset($verification['data']['status']) && 
                $verification['data']['status'] === 'successful' &&
                $verification['data']['amount'] >= $payment->amount &&
                $verification['data']['currency'] === $payment->currency) {
                
                Log::info('Payment verification passed all checks');
                
                // Payment verified successfully
                $payment->update([
                    'status' => 'completed',
                    'flutterwave_reference' => $verification['data']['flw_ref'] ?? null,
                ]);
                
                // Get pricing plan details
                $pricingPlan = PricingPlan::where('key', $payment->plan_type)->first();
                
                // Create subscription
                Subscription::create([
                    'user_id' => $payment->user_id,
                    'payment_id' => $payment->id,
                    'plan_type' => $payment->plan_type,
                    'starts_at' => now(),
                    'expires_at' => $payment->expires_at,
                    'is_active' => true,
                ]);

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
                    Log::info('Payment notification sent', ['user_id' => $user->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to send payment notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }

                Log::info('Payment completed successfully', [
                    'tx_ref' => $transactionId,
                    'user_id' => $user->id,
                    'plan' => $payment->plan_type,
                    'subscription_type' => $user->subscription_type
                ]);

                return redirect()->route('dashboard')->with('success', 'Payment successful! Your account has been upgraded to ' . strtoupper($user->subscription_type ?? 'PREMIUM') . '. Check your email for details.');
            } else {
                // Verification failed
                Log::warning('Payment verification failed - one or more checks did not pass', [
                    'tx_ref' => $transactionId,
                    'checks' => [
                        'verification_status' => $verification['status'] === 'success',
                        'data_status_exists' => isset($verification['data']['status']),
                        'data_status_successful' => ($verification['data']['status'] ?? '') === 'successful',
                        'amount_match' => ($verification['data']['amount'] ?? 0) >= $payment->amount,
                        'currency_match' => ($verification['data']['currency'] ?? '') === $payment->currency,
                    ]
                ]);
                
                $payment->update(['status' => 'failed']);
                return redirect()->route('pricing')->with('error', 'Payment verification failed. Please contact support if you were charged.');
            }
        }
        
        // For other payment methods or cancelled payments
        if ($status === 'successful') {
            $payment->update(['status' => 'completed']);
            
            // Get pricing plan details
            $pricingPlan = PricingPlan::where('key', $payment->plan_type)->first();
            
            // Create subscription
            Subscription::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'plan_type' => $payment->plan_type,
                'starts_at' => now(),
                'expires_at' => $payment->expires_at,
                'is_active' => true,
            ]);

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
                Log::error('Failed to send payment notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Payment successful! Your account has been upgraded to ' . strtoupper($user->subscription_type ?? 'PREMIUM') . '. Check your email for details.');
        } else {
            // Payment was cancelled or failed
            $payment->update(['status' => $status === 'cancelled' ? 'cancelled' : 'failed']);
            
            Log::info('Payment not successful', [
                'tx_ref' => $transactionId,
                'status' => $status
            ]);
            
            return redirect()->route('pricing')->with('error', 'Payment was ' . $status . '. Please try again.');
        }
    }

    public function cryptoPayment($transactionId)
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();
        
        if (!$payment) {
            return redirect()->route('pricing')->with('error', 'Payment not found');
        }

        return view('payment.crypto', compact('payment'));
    }

    public function mockPayment($txRef)
    {
        $payment = Payment::where('transaction_id', $txRef)->first();
        
        if (!$payment) {
            return redirect()->route('pricing')->with('error', 'Payment not found');
        }

        return view('payment.mock', compact('payment'));
    }

    private function initializePayment($payment, $paymentMethod, $cryptoType = null)
    {
        switch ($paymentMethod) {
            case 'flutterwave':
                return $this->initializeFlutterwavePayment($payment);
            case 'paypal':
                return $this->initializePayPalPayment($payment);
            case 'skrill':
                return $this->initializeSkrillPayment($payment);
            case 'crypto':
                return $this->initializeCryptoPayment($payment, $cryptoType);
            default:
                throw new \Exception('Unsupported payment method');
        }
    }

    private function initializeFlutterwavePayment($payment)
    {
        $flutterwaveData = [
            'tx_ref' => $payment->transaction_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'redirect_url' => route('payment.callback'),
            'customer' => [
                'email' => $payment->user->email,
                'name' => $payment->user->name,
                'phone_number' => $payment->user->phone ?? '',
            ],
            'customizations' => [
                'title' => 'Football Predictions Premium',
                'description' => 'Premium subscription for ' . $payment->plan_type,
            ],
        ];

        Log::info('Initializing Flutterwave payment', [
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'user_email' => $payment->user->email
        ]);

        $flutterwaveService = new FlutterwaveService();
        $response = $flutterwaveService->initializePayment($flutterwaveData);

        Log::info('Flutterwave response received', [
            'status' => $response['status'] ?? 'unknown',
            'has_link' => isset($response['data']['link'])
        ]);

        if ($response['status'] === 'success' && isset($response['data']['link'])) {
            if (isset($response['data']['reference'])) {
                $payment->update(['flutterwave_reference' => $response['data']['reference']]);
            }
            
            Log::info('Redirecting to Flutterwave checkout', [
                'link' => $response['data']['link']
            ]);
            
            return $response['data']['link'];
        }

        $errorMessage = $response['message'] ?? 'Failed to initialize Flutterwave payment';
        Log::error('Flutterwave initialization failed', [
            'error' => $errorMessage,
            'response' => $response
        ]);
        
        throw new \Exception($errorMessage);
    }

    private function initializePayPalPayment($payment)
    {
        // PayPal integration would go here
        // For now, return a mock URL
        $payment->update(['paypal_order_id' => 'PAYPAL_' . Str::random(10)]);
        return route('payment.mock', ['tx_ref' => $payment->transaction_id]);
    }

    private function initializeSkrillPayment($payment)
    {
        // Skrill integration would go here
        // For now, return a mock URL
        $payment->update(['skrill_transaction_id' => 'SKRILL_' . Str::random(10)]);
        return route('payment.mock', ['tx_ref' => $payment->transaction_id]);
    }

    private function initializeCryptoPayment($payment, $cryptoType)
    {
        // Generate crypto wallet address and amount
        $cryptoAddress = $this->generateCryptoAddress($cryptoType);
        $cryptoAmount = $this->convertToCrypto($payment->amount, $payment->currency, $cryptoType);
        
        $payment->update([
            'crypto_address' => $cryptoAddress,
            'crypto_amount' => $cryptoAmount,
            'crypto_type' => $cryptoType,
        ]);

        // For development, redirect to mock payment
        return route('payment.mock', ['tx_ref' => $payment->transaction_id]);
    }

    private function generateCryptoAddress($cryptoType)
    {
        // This would integrate with actual crypto wallet services
        $addresses = [
            'BTC' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
            'USDT' => 'TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE',
            'ETH' => '0x742d35Cc6634C0532925a3b8D4C9db96C4b4d8b6',
            'BNB' => 'bnb1grpf0955h0ykzq3ar5nmum7y6gdfl6lxfn46h2',
            'TRX' => 'TLyqzVGLV1srkB7dToTAEqgDSfPtXRJZYH',
        ];

        return $addresses[$cryptoType] ?? 'Address not available';
    }

    private function convertToCrypto($amount, $currency, $cryptoType)
    {
        // This would integrate with real-time crypto price APIs
        $rates = [
            'BTC' => ['USD' => 45000, 'EUR' => 38000, 'GBP' => 32000],
            'USDT' => ['USD' => 1, 'EUR' => 0.85, 'GBP' => 0.72],
            'ETH' => ['USD' => 3000, 'EUR' => 2500, 'GBP' => 2100],
            'BNB' => ['USD' => 300, 'EUR' => 250, 'GBP' => 210],
            'TRX' => ['USD' => 0.08, 'EUR' => 0.07, 'GBP' => 0.06],
        ];

        $rate = $rates[$cryptoType][$currency] ?? 1;
        return round($amount / $rate, 8);
    }

    private function getPlanDuration($planType)
    {
        return match($planType) {
            '3_days' => 3,
            '1_week' => 7,
            '1_month' => 30,
            '3_months' => 90,
            default => 0,
        };
    }

}
