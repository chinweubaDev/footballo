@extends('layouts.app')

@section('title', 'Payment Details - Football Predictions')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Payment Details</h1>
            <p class="text-xl text-gray-600 mb-6">Complete your payment using the details below</p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
        @endif

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Order Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Plan Details</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Plan Name</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $pricingPlan->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Duration</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $pricingPlan->duration_days }} days access</p>
                        </div>
                        @if($pricingPlan->features)
                        <div>
                            <p class="text-sm text-gray-600 mb-2">What's Included</p>
                            <ul class="space-y-1">
                                @foreach($pricingPlan->features as $feature)
                                <li class="flex items-center text-sm text-gray-700">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    {{ $feature }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Plan:</span>
                            <span class="font-semibold">{{ $pricingPlan->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Duration:</span>
                            <span class="font-semibold">{{ $pricingPlan->duration_days }} days</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-green-600">
                            <span>Total:</span>
                            <span>{{ $currency }} {{ number_format($amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Details -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 bg-{{ $paymentMethod->color }}-100 rounded-full flex items-center justify-center mr-4">
                    <i class="{{ $paymentMethod->icon }} text-{{ $paymentMethod->color }}-600 text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">{{ $paymentMethod->display_name }}</h2>
                    <p class="text-gray-600">{{ $paymentMethod->description }}</p>
                </div>
            </div>

            @if($paymentMethod->type === 'crypto')
                <!-- Cryptocurrency Payment Details -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Cryptocurrency Payment Instructions</h3>
                    
                    @if($paymentMethod->config && isset($paymentMethod->config['address']))
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Send {{ $paymentMethod->crypto_type }} to this address:</label>
                        <div class="flex items-center space-x-2">
                            <input type="text" 
                                   value="{{ $paymentMethod->config['address'] }}" 
                                   readonly 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm font-mono"
                                   id="cryptoAddress">
                            <button onclick="copyToClipboard('cryptoAddress')" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    @endif

                    @if($paymentMethod->config && isset($paymentMethod->config['network']))
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Network:</label>
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                            {{ strtoupper($paymentMethod->config['network']) }}
                        </span>
                    </div>
                    @endif

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="text-sm font-medium text-yellow-800">Important Instructions</h4>
                                <ul class="text-sm text-yellow-700 mt-2 space-y-1">
                                    <li>• Send exactly {{ $currency }} {{ number_format($amount) }} worth of {{ $paymentMethod->crypto_type }}</li>
                                    <li>• Use the correct network ({{ strtoupper($paymentMethod->config['network'] ?? 'N/A') }})</li>
                                    <li>• Include a unique memo/note with your email address</li>
                                    <li>• Wait for 3 confirmations before access is granted</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex">
                            <i class="fas fa-info-circle text-green-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="text-sm font-medium text-green-800">After Payment</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    Once your payment is confirmed, you'll receive an email with your premium access details. 
                                    Access will be activated within 24 hours.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($paymentMethod->type === 'flutterwave')
                <!-- Flutterwave Payment Details -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Flutterwave Payment</h3>
                    <p class="text-gray-600 mb-4">You will be redirected to Flutterwave's secure payment page to complete your transaction.</p>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-credit-card text-blue-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800">Supported Payment Methods</h4>
                                <ul class="text-sm text-blue-700 mt-2 space-y-1">
                                    <li>• Credit/Debit Cards (Visa, Mastercard, American Express)</li>
                                    <li>• Bank Transfer</li>
                                    <li>• Mobile Money</li>
                                    <li>• USSD</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <button onclick="proceedToFlutterwave()" 
                            class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-150 font-medium">
                        <i class="fas fa-credit-card mr-2"></i>
                        Proceed to Payment
                    </button>
                </div>

            @elseif($paymentMethod->type === 'paypal')
                <!-- PayPal Payment Details -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">PayPal Payment</h3>
                    <p class="text-gray-600 mb-4">You will be redirected to PayPal to complete your payment securely.</p>
                    
                    @if($paymentMethod->config && isset($paymentMethod->config['email']))
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <i class="fab fa-paypal text-blue-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800">PayPal Account</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    Payment will be processed through: {{ $paymentMethod->config['email'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <button onclick="proceedToPayPal()" 
                            class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-150 font-medium">
                        <i class="fab fa-paypal mr-2"></i>
                        Pay with PayPal
                    </button>
                </div>

            @elseif($paymentMethod->type === 'skrill')
                <!-- Skrill Payment Details -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Skrill Payment</h3>
                    <p class="text-gray-600 mb-4">You will be redirected to Skrill to complete your payment securely.</p>
                    
                    @if($paymentMethod->config && isset($paymentMethod->config['email']))
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <i class="fas fa-wallet text-orange-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="text-sm font-medium text-orange-800">Skrill Account</h4>
                                <p class="text-sm text-orange-700 mt-1">
                                    Payment will be processed through: {{ $paymentMethod->config['email'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <button onclick="proceedToSkrill()" 
                            class="w-full bg-orange-600 text-white py-3 px-6 rounded-lg hover:bg-orange-700 transition duration-150 font-medium">
                        <i class="fas fa-wallet mr-2"></i>
                        Pay with Skrill
                    </button>
                </div>
            @endif
        </div>

        <!-- Back to Payment Methods -->
        <div class="text-center">
            <a href="{{ route('payment.methods', ['plan' => $plan, 'amount' => $amount, 'currency' => $currency]) }}" 
               class="text-gray-600 hover:text-gray-800 underline">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Payment Methods
            </a>
        </div>
    </div>
</div>

<!-- Hidden form for payment initialization -->
<form id="paymentForm" action="{{ route('payment.initialize') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="payment_method" id="paymentMethod">
    <input type="hidden" name="plan_type" value="{{ $plan }}">
    <input type="hidden" name="amount" value="{{ $amount }}">
    <input type="hidden" name="currency" value="{{ $currency }}">
</form>

<script>
const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
const loginUrl = '{{ route("login") }}';

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show success message
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
    button.classList.add('bg-green-600');
    button.classList.remove('bg-blue-600');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('bg-green-600');
        button.classList.add('bg-blue-600');
    }, 2000);
}

function proceedToFlutterwave() {
    console.log('Proceeding to Flutterwave payment');
    console.log('Is authenticated:', isAuthenticated);
    
    if (!isAuthenticated) {
        alert('Please login to continue with payment');
        window.location.href = loginUrl + '?redirect=' + encodeURIComponent(window.location.href);
        return;
    }
    
    const form = document.getElementById('paymentForm');
    console.log('Form found:', form);
    
    const methodInput = document.getElementById('paymentMethod');
    console.log('Method input found:', methodInput);
    
    if (form && methodInput) {
        methodInput.value = 'flutterwave';
        console.log('Payment method set to:', methodInput.value);
        console.log('Submitting payment form');
        form.submit();
    } else {
        console.error('Form or method input not found!');
        alert('Error: Payment form not found. Please refresh the page and try again.');
    }
}

function proceedToPayPal() {
    console.log('Proceeding to PayPal payment');
    
    if (!isAuthenticated) {
        alert('Please login to continue with payment');
        window.location.href = loginUrl + '?redirect=' + encodeURIComponent(window.location.href);
        return;
    }
    
    const form = document.getElementById('paymentForm');
    const methodInput = document.getElementById('paymentMethod');
    
    if (form && methodInput) {
        methodInput.value = 'paypal';
        console.log('Submitting payment form');
        form.submit();
    } else {
        console.error('Form or method input not found!');
        alert('Error: Payment form not found. Please refresh the page and try again.');
    }
}

function proceedToSkrill() {
    console.log('Proceeding to Skrill payment');
    
    if (!isAuthenticated) {
        alert('Please login to continue with payment');
        window.location.href = loginUrl + '?redirect=' + encodeURIComponent(window.location.href);
        return;
    }
    
    const form = document.getElementById('paymentForm');
    const methodInput = document.getElementById('paymentMethod');
    
    if (form && methodInput) {
        methodInput.value = 'skrill';
        console.log('Submitting payment form');
        form.submit();
    } else {
        console.error('Form or method input not found!');
        alert('Error: Payment form not found. Please refresh the page and try again.');
    }
}
</script>
@endsection
