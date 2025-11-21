@extends('layouts.app')

@section('title', 'Crypto Payment')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                <i class="fas fa-coins text-yellow-500 mr-3"></i>
                Cryptocurrency Payment
            </h1>
            <p class="text-lg text-gray-600">Complete your payment using {{ $payment->crypto_type }}</p>
        </div>

        <!-- Payment Details Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-4">
                    @if($payment->crypto_type === 'BTC')
                        <i class="fab fa-bitcoin text-yellow-500 text-2xl"></i>
                    @elseif($payment->crypto_type === 'ETH')
                        <i class="fab fa-ethereum text-blue-500 text-2xl"></i>
                    @elseif($payment->crypto_type === 'USDT')
                        <i class="fas fa-coins text-green-500 text-2xl"></i>
                    @elseif($payment->crypto_type === 'BNB')
                        <i class="fas fa-coins text-yellow-500 text-2xl"></i>
                    @elseif($payment->crypto_type === 'TRX')
                        <i class="fas fa-coins text-red-500 text-2xl"></i>
                    @else
                        <i class="fas fa-coins text-gray-500 text-2xl"></i>
                    @endif
                </div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $payment->crypto_type }} Payment</h2>
            </div>

            <!-- Payment Information -->
            <div class="space-y-6">
                <!-- Amount to Pay -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-2">Amount to Pay</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $payment->crypto_amount }} {{ $payment->crypto_type }}</p>
                        <p class="text-sm text-gray-500 mt-1">≈ {{ $payment->currency }} {{ $payment->amount }}</p>
                    </div>
                </div>

                <!-- Wallet Address -->
                <div class="bg-blue-50 rounded-lg p-6">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-2">Send to this {{ $payment->crypto_type }} address:</p>
                        <div class="bg-white rounded-lg p-4 border-2 border-blue-200">
                            <p class="text-sm font-mono text-gray-800 break-all">{{ $payment->crypto_address }}</p>
                        </div>
                        <button onclick="copyAddress()" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out">
                            <i class="fas fa-copy mr-2"></i>Copy Address
                        </button>
                    </div>
                </div>

                <!-- QR Code Placeholder -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-4">Scan QR Code</p>
                        <div class="inline-block bg-gray-100 rounded-lg p-8">
                            <i class="fas fa-qrcode text-gray-400 text-4xl"></i>
                            <p class="text-xs text-gray-500 mt-2">QR Code would be generated here</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <div class="bg-yellow-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                        Payment Instructions
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            Send exactly <strong>{{ $payment->crypto_amount }} {{ $payment->crypto_type }}</strong> to the address above
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            Include the transaction ID <strong>{{ $payment->transaction_id }}</strong> in the memo/note field if possible
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            Payment will be confirmed automatically once received (usually within 10-30 minutes)
                        </li>
                        <li class="flex items-start">
                            <span class="text-yellow-600 mr-2">•</span>
                            Do not send from an exchange that doesn't support the {{ $payment->crypto_type }} network
                        </li>
                    </ul>
                </div>

                <!-- Transaction Details -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction Details</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600">Transaction ID:</p>
                            <p class="font-mono text-gray-900">{{ $payment->transaction_id }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Plan:</p>
                            <p class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->plan_type)) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600">Status:</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-600">Expires:</p>
                            <p class="text-gray-900">{{ $payment->expires_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('pricing') }}" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition duration-150 ease-in-out text-center">
                <i class="fas fa-arrow-left mr-2"></i>Back to Pricing
            </a>
            <button onclick="checkPaymentStatus()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                <i class="fas fa-sync-alt mr-2"></i>Check Payment Status
            </button>
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                Need Help?
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                    <p class="font-medium text-gray-900 mb-2">Payment Issues:</p>
                    <p>If you're having trouble with the payment, please contact our support team with your transaction ID.</p>
                </div>
                <div>
                    <p class="font-medium text-gray-900 mb-2">Confirmation Time:</p>
                    <p>Cryptocurrency payments typically take 10-30 minutes to confirm, depending on network congestion.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyAddress() {
    const address = '{{ $payment->crypto_address }}';
    navigator.clipboard.writeText(address).then(function() {
        alert('Address copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}

function checkPaymentStatus() {
    // This would make an AJAX request to check payment status
    alert('Checking payment status... This would integrate with blockchain APIs to verify the transaction.');
}

// Auto-refresh every 30 seconds to check payment status
setInterval(function() {
    // This would check if payment has been confirmed
    console.log('Checking payment status...');
}, 30000);
</script>
@endsection
