<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    protected $publicKey;
    protected $secretKey;
    protected $encryptionKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->publicKey = config('services.flutterwave.public_key');
        $this->secretKey = config('services.flutterwave.secret_key');
        $this->encryptionKey = config('services.flutterwave.encryption_key');
        $this->baseUrl = 'https://api.flutterwave.com/v3';
    }

    /**
     * Initialize payment
     */
    public function initializePayment($data)
    {
        try {
            // Check if we're in development mode (no API keys configured)
            if (empty($this->secretKey) || $this->secretKey === 'your_flutterwave_secret_key_here') {
                Log::warning('Flutterwave: Using mock payment - API keys not configured');
                
                // Return mock response for development
                return [
                    'status' => 'success',
                    'data' => [
                        'reference' => 'FLW_MOCK_' . time(),
                        'link' => route('payment.mock', ['tx_ref' => $data['tx_ref']])
                    ],
                    'message' => 'Using mock payment gateway (API keys not configured)'
                ];
            }

            Log::info('Flutterwave: Initializing payment', [
                'tx_ref' => $data['tx_ref'],
                'amount' => $data['amount'],
                'currency' => $data['currency']
            ]);

            $payload = [
                'tx_ref' => $data['tx_ref'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'redirect_url' => $data['redirect_url'],
                'customer' => $data['customer'],
                'customizations' => $data['customizations'],
                'payment_options' => 'card,mobilemoney,ussd,banktransfer',
                'meta' => [
                    'consumer_id' => $data['customer']['email'],
                    'consumer_mac' => '92a3b912c1d462375b7a04f87a60c7649380cda5e2b904a5e5c824f0538b5e2'
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/payments', $payload);

            Log::info('Flutterwave: API Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if ($result['status'] === 'success') {
                    return [
                        'status' => 'success',
                        'data' => [
                            'reference' => $result['data']['reference'] ?? $data['tx_ref'],
                            'link' => $result['data']['link']
                        ]
                    ];
                }
            }

            Log::error('Flutterwave: Payment initialization failed', [
                'payload' => $payload,
                'response' => $response->body()
            ]);

            return [
                'status' => 'error',
                'message' => 'Payment initialization failed: ' . ($response->json()['message'] ?? 'Unknown error')
            ];
        } catch (\Exception $e) {
            Log::error('Flutterwave: Exception during payment initialization', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            return [
                'status' => 'error',
                'message' => 'Payment initialization failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment
     */
    public function verifyPayment($transactionId)
    {
        try {
            // Check if we're in development mode (no API keys configured)
            if (empty($this->secretKey) || $this->secretKey === 'your_flutterwave_secret_key_here') {
                Log::warning('Flutterwave: Using mock verification - API keys not configured');
                
                // Get payment details to return correct amount and currency
                $payment = \App\Models\Payment::where('transaction_id', $transactionId)->first();
                
                // Return mock verification for development with actual payment data
                return [
                    'status' => 'success',
                    'data' => [
                        'id' => rand(100000, 999999),
                        'tx_ref' => $transactionId,
                        'flw_ref' => 'FLW_MOCK_' . time(),
                        'amount' => $payment ? $payment->amount : 100,
                        'currency' => $payment ? $payment->currency : 'NGN',
                        'status' => 'successful',
                        'payment_type' => 'card',
                        'created_at' => now()->toIso8601String(),
                    ]
                ];
            }

            Log::info('Flutterwave: Verifying payment', ['transaction_id' => $transactionId]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/transactions/' . $transactionId . '/verify');

            Log::info('Flutterwave: Verification response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if ($result['status'] === 'success') {
                    return [
                        'status' => 'success',
                        'data' => $result['data']
                    ];
                }
            }

            Log::error('Flutterwave: Payment verification failed', [
                'transaction_id' => $transactionId,
                'response' => $response->body()
            ]);

            return [
                'status' => 'error',
                'message' => 'Payment verification failed: ' . ($response->json()['message'] ?? 'Unknown error')
            ];
        } catch (\Exception $e) {
            Log::error('Flutterwave: Exception during payment verification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_id' => $transactionId
            ]);

            return [
                'status' => 'error',
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get transaction details
     */
    public function getTransaction($transactionId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/transactions/' . $transactionId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Flutterwave: Failed to get transaction', [
                'transaction_id' => $transactionId,
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Flutterwave: Exception while getting transaction', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ]);
            return null;
        }
    }

    /**
     * Get all transactions
     */
    public function getTransactions($page = 1, $perPage = 20)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/transactions', [
                'page' => $page,
                'perPage' => $perPage
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Flutterwave: Failed to get transactions', [
                'page' => $page,
                'per_page' => $perPage,
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Flutterwave: Exception while getting transactions', [
                'error' => $e->getMessage(),
                'page' => $page,
                'per_page' => $perPage
            ]);
            return null;
        }
    }

    /**
     * Refund transaction
     */
    public function refundTransaction($transactionId, $amount = null)
    {
        try {
            $payload = [
                'tx_ref' => $transactionId
            ];

            if ($amount) {
                $payload['amount'] = $amount;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/transactions/' . $transactionId . '/refund', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Flutterwave: Refund failed', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Flutterwave: Exception during refund', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
                'amount' => $amount
            ]);
            return null;
        }
    }

    /**
     * Test connection
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/transactions');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Flutterwave: Connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
