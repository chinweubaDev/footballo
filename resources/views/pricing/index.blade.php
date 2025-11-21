@extends('layouts.app')

@section('title', 'Pricing Plans - Football Predictions')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Choose Your VIP Plan</h1>
            <p class="text-xl text-gray-600 mb-6">Get access to exclusive VIP and VVIP predictions and expert analysis</p>
            
            <!-- Country Selection -->
            <div class="mb-6">
                <label for="countrySelect" class="block text-sm font-medium text-gray-700 mb-2">Select Your Country</label>
                <select id="countrySelect" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    @foreach($availableCountries as $key => $label)
                        <option value="{{ $key }}" {{ $selectedCountry === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="inline-flex items-center bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                <i class="fas fa-map-marker-alt mr-2"></i>
                Pricing for <span id="currentCountry">{{ $selectedCountry }}</span>
            </div>
        </div>

        <!-- VIP Plans Section -->
<div class="mb-16">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">VIP Plans</h2>
        <p class="text-lg text-gray-600">Premium football predictions and expert analysis</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($pricingPlans->filter(fn($plan) => Str::startsWith($plan->key, 'vip_')) as $plan)
        @php
            $features = is_string($plan->features) ? json_decode($plan->features, true) : $plan->features;
        @endphp

        <div id="plan-{{ $plan->key }}" 
             class="bg-white rounded-lg shadow-lg p-6 relative {{ $plan->key === 'vip_1_month' ? 'ring-2 ring-blue-500 transform scale-105' : '' }}">
             
            @if($plan->key === 'vip_1_month')
            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                <span class="bg-blue-500 text-white text-xs font-medium px-3 py-1 rounded-full">Most Popular</span>
            </div>
            @endif

            <div class="text-center mb-6">
                <div class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full inline-block mb-3">
                    VIP
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ $plan->name }}
                </h3>
                <div class="text-3xl font-bold text-gray-900 mb-1" id="price-{{ $plan->key }}">
                    <span class="currency-{{ $plan->key }}">{{ $plan->getCurrencyForCountry($selectedCountry) }}</span> <span class="amount-{{ $plan->key }}">{{ number_format($plan->getPriceForCountry($selectedCountry)) }}</span>
                </div>
                <p class="text-sm text-gray-500">
                    {{ $plan->duration_days }} days access
                </p>
            </div>

            <ul class="space-y-3 mb-6">
                @if(!empty($features))
                    @foreach($features as $feature)
                    <li class="flex items-center">
                        <i class="fas fa-check text-blue-500 mr-3"></i>
                        <span class="text-sm text-gray-600">{{ $feature }}</span>
                    </li>
                    @endforeach
                @else
                    <li class="text-sm text-gray-400 italic">No features listed</li>
                @endif
            </ul>

            <a href="{{ route('payment.methods', [
                'plan' => $plan->key,
                'amount' => $plan->getPriceForCountry($selectedCountry),
                'currency' => $plan->getCurrencyForCountry($selectedCountry)
            ]) }}" 
               class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out font-semibold text-center block link-{{ $plan->key }}">
                Subscribe Now
            </a>
        </div>
        @endforeach
    </div>
</div>

<!-- VVIP Plans Section -->
<div class="mb-16">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">VVIP Plans</h2>
        <p class="text-lg text-gray-600">Ultimate premium predictions with exclusive access</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($pricingPlans->filter(fn($plan) => Str::startsWith($plan->key, 'vvip_')) as $plan)
        @php
            $features = is_string($plan->features) ? json_decode($plan->features, true) : $plan->features;
        @endphp

        <div id="plan-{{ $plan->key }}" 
             class="bg-white rounded-lg shadow-lg p-6 relative {{ $plan->key === 'vvip_1_month' ? 'ring-2 ring-purple-500 transform scale-105' : '' }}">
             
            @if($plan->key === 'vvip_1_month')
            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                <span class="bg-purple-500 text-white text-xs font-medium px-3 py-1 rounded-full">Most Popular</span>
            </div>
            @endif

            <div class="text-center mb-6">
                <div class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full inline-block mb-3">
                    VVIP
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ $plan->name }}
                </h3>
                <div class="text-3xl font-bold text-gray-900 mb-1" id="price-{{ $plan->key }}">
                    <span class="currency-{{ $plan->key }}">{{ $plan->getCurrencyForCountry($selectedCountry) }}</span> <span class="amount-{{ $plan->key }}">{{ number_format($plan->getPriceForCountry($selectedCountry)) }}</span>
                </div>
                <p class="text-sm text-gray-500">
                    {{ $plan->duration_days }} days access
                </p>
            </div>

            <ul class="space-y-3 mb-6">
                @if(!empty($features))
                    @foreach($features as $feature)
                    <li class="flex items-center">
                        <i class="fas fa-check text-purple-500 mr-3"></i>
                        <span class="text-sm text-gray-600">{{ $feature }}</span>
                    </li>
                    @endforeach
                @else
                    <li class="text-sm text-gray-400 italic">No features listed</li>
                @endif
            </ul>

            <a href="{{ route('payment.methods', [
                'plan' => $plan->key,
                'amount' => $plan->getPriceForCountry($selectedCountry),
                'currency' => $plan->getCurrencyForCountry($selectedCountry)
            ]) }}" 
               class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition duration-150 ease-in-out font-semibold text-center block link-{{ $plan->key }}">
                Subscribe Now
            </a>
        </div>
        @endforeach
    </div>
</div>




        <!-- Features Section -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-12">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">What You Get</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Expert Analysis</h3>
                    <p class="text-gray-600">Get detailed analysis from our team of football experts with years of experience.</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bullseye text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">High Accuracy</h3>
                    <p class="text-gray-600">Our predictions have a proven track record with high win rates and consistent results.</p>
                </div>
                <div class="text-center">
                    <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Real-time Updates</h3>
                    <p class="text-gray-600">Get instant notifications and updates on your favorite matches and predictions.</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">Frequently Asked Questions</h2>
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">How do I pay?</h3>
                    <p class="text-gray-600">We accept multiple payment methods including:</p>
                    <ul class="list-disc list-inside text-gray-600 mt-2 space-y-1">
                        <li><strong>Flutterwave:</strong> Cards, bank transfers, and mobile money</li>
                        <li><strong>PayPal:</strong> PayPal account payments</li>
                        <li><strong>Skrill:</strong> Digital wallet payments</li>
                        <li><strong>Cryptocurrency:</strong> Bitcoin (BTC), Tether (USDT), Ethereum (ETH), Binance Coin (BNB), and TRON (TRX)</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Can I cancel my subscription?</h3>
                    <p class="text-gray-600">Yes, you can cancel your subscription at any time. However, refunds are not available for the current billing period.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">What if I'm not satisfied?</h3>
                    <p class="text-gray-600">We offer a 7-day money-back guarantee for all new subscribers. Contact our support team for assistance.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Do you offer refunds?</h3>
                    <p class="text-gray-600">Refunds are available within 7 days of subscription for new users. Contact support for more information.</p>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// Country selection change handler
document.getElementById('countrySelect').addEventListener('change', function() {
    const selectedCountry = this.value;
    updatePricingForCountry(selectedCountry);
});

function updatePricingForCountry(country) {
    console.log('Updating pricing for country:', country);
    
    // Show loading state
    const pricingCards = document.querySelectorAll('[id^="plan-"]');
    console.log('Found pricing cards:', pricingCards.length);
    pricingCards.forEach(card => {
        card.style.opacity = '0.6';
        card.style.pointerEvents = 'none';
    });

    // Update current country display
    document.getElementById('currentCountry').textContent = country;

    // Fetch new pricing
    fetch(`/pricing/country?country=${country}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Pricing data received:', data);
            if (data.status === 'success') {
                // Update pricing for each plan
                Object.keys(data.pricing).forEach(planKey => {
                    const plan = data.pricing[planKey];
                    console.log(`Updating plan ${planKey}:`, plan);
                    
                    // Update currency
                    const currencyElement = document.querySelector(`.currency-${planKey}`);
                    if (currencyElement) {
                        currencyElement.textContent = plan.currency;
                        console.log(`Updated currency for ${planKey}: ${plan.currency}`);
                    } else {
                        console.warn(`Currency element not found for plan: ${planKey}`);
                    }
                    
                    // Update amount
                    const amountElement = document.querySelector(`.amount-${planKey}`);
                    if (amountElement) {
                        amountElement.textContent = plan.price.toLocaleString();
                        console.log(`Updated amount for ${planKey}: ${plan.price.toLocaleString()}`);
                    } else {
                        console.warn(`Amount element not found for plan: ${planKey}`);
                    }
                    
                    // Update the link with new amount and currency
                    const link = document.querySelector(`.link-${planKey}`);
                    if (link) {
                        const url = new URL(link.href);
                        url.searchParams.set('amount', plan.price);
                        url.searchParams.set('currency', plan.currency);
                        link.href = url.toString();
                        console.log(`Updated link for ${planKey}: ${link.href}`);
                    } else {
                        console.warn(`Link not found for plan: ${planKey}`);
                    }
                });
            } else {
                console.error('Pricing update failed:', data);
            }
        })
        .catch(error => {
            console.error('Error updating pricing:', error);
            alert('Error updating pricing. Please try again.');
        })
        .finally(() => {
            // Remove loading state
            pricingCards.forEach(card => {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            });
        });
}
</script>
@endsection
