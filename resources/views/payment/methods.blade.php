@extends('layouts.app')

@section('title', 'Choose Payment Method - Football Predictions')

@section('content')
<div class="py-12" data-plan="{{ $plan }}" data-amount="{{ $amount }}" data-currency="{{ $currency }}">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Choose Your Payment Method</h1>
            <p class="text-xl text-gray-600 mb-6">Select your preferred payment method to continue</p>
        </div>

        <!-- Plan Summary -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Order Summary</h2>
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
                <div class="text-right ml-8">
                    <p class="text-3xl font-bold text-green-600" id="planAmount">{{ $currency }} {{ number_format($amount) }}</p>
                    <p class="text-sm text-gray-500">One-time payment</p>
                    <div class="mt-4 p-3 bg-green-50 rounded-lg">
                        <p class="text-sm text-green-800 font-medium">Premium Access</p>
                        <p class="text-xs text-green-600">Full features included</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @php
                $traditionalMethods = $paymentMethods->where('type', '!=', 'crypto');
                $cryptoMethods = $paymentMethods->where('type', 'crypto');
            @endphp
            
            <!-- Traditional Payment Methods -->
            @if($traditionalMethods->count() > 0)
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Traditional Payment Methods</h3>
                
                @foreach($traditionalMethods as $method)
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300 border-2 border-transparent hover:border-{{ $method->color }}-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="w-12 h-12 bg-{{ $method->color }}-100 rounded-full flex items-center justify-center mr-4">
                                <i class="{{ $method->icon }} text-{{ $method->color }}-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900">{{ $method->display_name }}</h4>
                                <p class="text-sm text-gray-600 mb-2">{{ $method->description }}</p>
                                @if($method->config)
                                    @if($method->type === 'flutterwave')
                                        <p class="text-xs text-gray-500">Supports cards, bank transfer, mobile money</p>
                                    @elseif($method->type === 'paypal')
                                        <p class="text-xs text-gray-500">PayPal account and credit cards accepted</p>
                                    @elseif($method->type === 'skrill')
                                        <p class="text-xs text-gray-500">Digital wallet payment</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('payment.details', ['plan' => $plan, 'amount' => $amount, 'currency' => $currency, 'payment_method' => $method->type]) }}" 
                           class="bg-{{ $method->color }}-600 text-white px-6 py-2 rounded-lg hover:bg-{{ $method->color }}-700 transition duration-150 font-medium inline-block">
                            Choose
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Cryptocurrency Payment Methods -->
            @if($cryptoMethods->count() > 0)
            <div class="space-y-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Cryptocurrency Payments</h3>
                
                @foreach($cryptoMethods as $method)
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300 border-2 border-transparent hover:border-{{ $method->color }}-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="w-12 h-12 bg-{{ $method->color }}-100 rounded-full flex items-center justify-center mr-4">
                                <i class="{{ $method->icon }} text-{{ $method->color }}-600 text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900">{{ $method->display_name }}</h4>
                                <p class="text-sm text-gray-600 mb-2">{{ $method->description }}</p>
                                @if($method->config && isset($method->config['address']))
                                    <p class="text-xs text-gray-500">Address: {{ substr($method->config['address'], 0, 10) }}...{{ substr($method->config['address'], -6) }}</p>
                                @endif
                                @if($method->config && isset($method->config['network']))
                                    <p class="text-xs text-gray-500">Network: {{ strtoupper($method->config['network']) }}</p>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('payment.details', ['plan' => $plan, 'amount' => $amount, 'currency' => $currency, 'payment_method' => 'crypto', 'crypto_type' => $method->crypto_type]) }}" 
                           class="bg-{{ $method->color }}-600 text-white px-6 py-2 rounded-lg hover:bg-{{ $method->color }}-700 transition duration-150 font-medium inline-block">
                            Choose
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- What You're Paying For -->
        <div class="mt-12 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-1"></i>
                <div>
                    <h4 class="text-lg font-semibold text-blue-800 mb-2">What You're Paying For</h4>
                    <div class="text-blue-700 space-y-2">
                        <p><strong>{{ $pricingPlan->name }} Premium Access</strong> - {{ $pricingPlan->duration_days }} days of premium football predictions</p>
                        @if($pricingPlan->features)
                        <p class="text-sm">Including: {{ implode(', ', $pricingPlan->features) }}</p>
                        @endif
                        <p class="text-sm font-medium">Total: {{ $currency }} {{ number_format($amount) }} (One-time payment)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center">
                <i class="fas fa-shield-alt text-green-500 text-xl mr-3"></i>
                <div>
                    <h4 class="text-lg font-semibold text-green-800">Secure Payment Processing</h4>
                    <p class="text-green-700">All payments are processed securely. Your payment information is encrypted and protected.</p>
                </div>
            </div>
        </div>

        <!-- Back to Pricing -->
        <div class="mt-8 text-center">
            <a href="{{ route('pricing') }}" class="text-gray-600 hover:text-gray-800 underline">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Pricing Plans
            </a>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto mb-4"></div>
            <h3 class="text-lg font-medium text-gray-900">Processing Payment</h3>
            <p class="text-sm text-gray-600 mt-2">Please wait while we initialize your payment...</p>
        </div>
    </div>
</div>

@endsection
