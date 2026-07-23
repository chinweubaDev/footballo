@extends('layouts.app')

@section('title', 'Payment Details - Football Predictions')

@section('content')
<div class="bg-slate-950 min-h-screen pb-20">
    <!-- Details Hero -->
    <section class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 pt-24 pb-16 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #eab308 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="absolute -top-24 -right-24 w-[500px] h-[500px] bg-yellow-500/10 blur-[120px] rounded-full animate-pulse"></div>
        
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-[10px] font-black uppercase tracking-[0.2em] mb-8" data-aos="fade-down">
                <i class="fas fa-receipt mr-2"></i> Payment Details
            </div>
            <h1 class="text-4xl lg:text-5xl font-black text-white mb-6 tracking-tight" data-aos="fade-up">
                Complete Your <span class="bg-gradient-to-r from-yellow-300 via-yellow-500 to-yellow-600 bg-clip-text text-transparent italic">Activation</span>
            </h1>
            <p class="text-slate-400 max-w-xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Follow the instructions below to finalize your premium access. Your account will be upgraded immediately after verification.
            </p>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10">
        <!-- Error Messages -->
        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-6 py-4 rounded-2xl mb-8 flex items-start gap-4" role="alert">
            <i class="fas fa-exclamation-circle mt-1"></i>
            <div>
                <strong class="font-black uppercase tracking-widest text-xs">Correction Required</strong>
                <ul class="mt-2 list-disc list-inside text-sm font-medium opacity-80">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-6 py-4 rounded-2xl mb-8 flex items-center gap-4" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <span class="text-sm font-black uppercase tracking-widest">{{ session('error') }}</span>
        </div>
        @endif

        <!-- Order Summary Card -->
        <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white/10 mb-8 shadow-2xl" data-aos="fade-up">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex-1 w-full">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/5 text-slate-400 text-[10px] font-black uppercase tracking-widest mb-6">
                        Order Summary
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Subscription Plan</p>
                            <h3 class="text-xl font-black text-white leading-tight">{{ $pricingPlan->name }}</h3>
                            <p class="text-sm font-bold text-slate-400 mt-1">{{ $pricingPlan->duration_days }} Days Unlimited Access</p>
                        </div>
                        <div class="bg-slate-800/50 rounded-2xl p-6 border border-white/5">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Total Payable</p>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-lg font-bold text-yellow-500">{{ $currency }}</span>
                                <span class="text-4xl font-black text-white tracking-tighter">{{ number_format($amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Details -->
        <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white/10 mb-8 shadow-2xl" data-aos="fade-up" data-aos-delay="100">
            <div class="flex items-center mb-10 pb-6 border-b border-white/5">
                <div class="w-14 h-14 bg-{{ $paymentMethod->color }}-500/10 rounded-2xl flex items-center justify-center mr-5 border border-{{ $paymentMethod->color }}-500/20">
                    <i class="{{ $paymentMethod->icon }} text-{{ $paymentMethod->color }}-500 text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-white uppercase tracking-tight">{{ $paymentMethod->display_name }}</h2>
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">{{ $paymentMethod->type === 'crypto' ? 'Blockchain Payment' : 'Secure Gateway' }}</p>
                </div>
            </div>

            @if($paymentMethod->type === 'crypto')
                <!-- Cryptocurrency Payment Details -->
                <div class="space-y-8">
                    @if($paymentMethod->config && isset($paymentMethod->config['address']))
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3">Send {{ $paymentMethod->crypto_type }} to this address</label>
                        <div class="flex flex-col sm:flex-row items-stretch gap-3">
                            <div class="relative flex-1">
                                <input type="text" 
                                       value="{{ $paymentMethod->config['address'] }}" 
                                       readonly 
                                       class="w-full bg-slate-950 border border-white/10 text-white text-sm font-mono rounded-2xl px-6 py-4 focus:ring-2 focus:ring-blue-500/50 outline-none"
                                       id="cryptoAddress">
                            </div>
                            <button onclick="copyToClipboard('cryptoAddress')" 
                                    class="px-8 py-4 bg-blue-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-blue-500 transition-all shadow-lg hover:shadow-blue-500/20 flex items-center justify-center group">
                                <i class="fas fa-copy mr-2 transition-transform group-hover:scale-110"></i> Copy Address
                            </button>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @if($paymentMethod->config && isset($paymentMethod->config['network']))
                        <div class="bg-white/5 rounded-2xl p-5 border border-white/5">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Blockchain Network</p>
                            <span class="text-white font-black uppercase tracking-tight">
                                {{ strtoupper($paymentMethod->config['network']) }}
                            </span>
                        </div>
                        @endif
                        <div class="bg-white/5 rounded-2xl p-5 border border-white/5">
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Asset Type</p>
                            <span class="text-white font-black uppercase tracking-tight">{{ $paymentMethod->crypto_type }}</span>
                        </div>
                    </div>

                    <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-3xl p-6">
                        <div class="flex gap-4">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mt-1"></i>
                            <div>
                                <h4 class="text-xs font-black text-white uppercase tracking-widest mb-2">Critical Instructions</h4>
                                <ul class="text-xs text-slate-400 space-y-2">
                                    <li class="flex items-start gap-2">• <span class="bg-yellow-500/10 text-yellow-500 px-1 font-bold">Important:</span> Only send through the {{ strtoupper($paymentMethod->config['network'] ?? 'N/A') }} network. Assets sent via other networks will be lost.</li>
                                    <li>• Send the exact equivalent of {{ $currency }} {{ number_format($amount) }} at current rates.</li>
                                    <li>• 3 blockchain confirmations required for automatic activation.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-500/5 border border-blue-500/10 rounded-3xl p-6">
                        <div class="flex gap-4">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                            <div>
                                <h4 class="text-xs font-black text-white uppercase tracking-widest mb-2">Next Steps</h4>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    After transaction confirmation, your VIP access will be activated within 15-60 minutes. You will receive a notification email once complete.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($paymentMethod->type === 'flutterwave')
                <!-- Flutterwave Payment Details -->
                <div class="text-center py-4">
                    <p class="text-slate-400 mb-8 max-w-sm mx-auto">Click below to securely pay via Flutterwave. Supports Cards, Bank Transfer, and Mobile Money.</p>
                    
                    <button onclick="proceedToFlutterwave()" 
                            class="w-full sm:w-auto px-12 py-5 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 text-slate-950 font-black text-lg rounded-2xl hover:shadow-2xl hover:shadow-yellow-500/30 transition-all hover:-translate-y-1">
                        Securely Pay Now <i class="fas fa-arrow-right ml-3 text-sm opacity-50"></i>
                    </button>
                    
                    <div class="mt-10 flex flex-wrap justify-center gap-6 opacity-30">
                        <i class="fab fa-cc-visa text-2xl text-white"></i>
                        <i class="fab fa-cc-mastercard text-2xl text-white"></i>
                        <i class="fas fa-university text-2xl text-white"></i>
                        <i class="fas fa-mobile-alt text-2xl text-white"></i>
                    </div>
                </div>

            @elseif($paymentMethod->type === 'paypal')
                <!-- PayPal Payment Details -->
                <div class="text-center py-4">
                    <p class="text-slate-400 mb-8 max-w-sm mx-auto">You will be redirected to PayPal's secure gateway to complete your transaction.</p>
                    
                    <button onclick="proceedToPayPal()" 
                            class="w-full sm:w-auto px-12 py-5 bg-[#0070ba] text-white font-black text-lg rounded-2xl hover:shadow-2xl hover:shadow-[#0070ba]/30 transition-all hover:-translate-y-1">
                        <i class="fab fa-paypal mr-3"></i> Pay with PayPal
                    </button>
                </div>

            @elseif($paymentMethod->type === 'skrill')
                <!-- Skrill Payment Details -->
                <div class="text-center py-4">
                    <p class="text-slate-400 mb-8 max-w-sm mx-auto">Redirecting to Skrill for secure wallet-to-wallet or card payment.</p>
                    
                    <button onclick="proceedToSkrill()" 
                            class="w-full sm:w-auto px-12 py-5 bg-[#82245b] text-white font-black text-lg rounded-2xl hover:shadow-2xl hover:shadow-[#82245b]/30 transition-all hover:-translate-y-1">
                        <i class="fas fa-wallet mr-3"></i> Pay with Skrill
                    </button>
                </div>
            @elseif($paymentMethod->type === 'bank_transfer')
                <!-- Bank Transfer Payment Details -->
                <div class="space-y-8">
                    <div class="bg-emerald-500/5 border border-emerald-500/10 rounded-3xl p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Banking Institution</p>
                                    <h4 class="text-xl font-black text-white uppercase tracking-tight">{{ $paymentMethod->config['bank_name'] ?? 'N/A' }}</h4>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Account Identity</p>
                                    <h4 class="text-xl font-black text-white uppercase tracking-tight">{{ $paymentMethod->config['account_name'] ?? 'N/A' }}</h4>
                                </div>
                            </div>
                            
                            <div class="bg-slate-950/50 rounded-2xl p-6 border border-white/5 flex flex-col justify-center">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Target Account Number</p>
                                <div class="flex items-center gap-3">
                                    <input type="text" 
                                           value="{{ $paymentMethod->config['account_number'] ?? 'N/A' }}" 
                                           readonly 
                                           class="w-full bg-transparent text-2xl font-black text-emerald-500 tracking-tighter outline-none"
                                           id="accountNumber">
                                    <button onclick="copyToClipboard('accountNumber')" 
                                            class="p-3 bg-emerald-500/10 text-emerald-500 rounded-xl hover:bg-emerald-500 hover:text-white transition-all">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-500/5 border border-blue-500/10 rounded-3xl p-6">
                        <div class="flex gap-4">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                            <div>
                                <h4 class="text-xs font-black text-white uppercase tracking-widest mb-2">Verification Protocol</h4>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    {{ $paymentMethod->config['instructions'] ?? 'Transfer the exact amount and save your receipt for verification.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-4">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-6">Completed the transfer?</p>
                        <button onclick="confirmBankTransfer()" 
                                class="w-full sm:w-auto px-12 py-5 bg-emerald-600 text-white font-black text-lg rounded-2xl hover:shadow-2xl hover:shadow-emerald-500/30 transition-all hover:-translate-y-1">
                            Confirm Transmission <i class="fas fa-check-circle ml-3"></i>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Back to Payment Methods -->
        <div class="mt-12 text-center">
            <a href="{{ route('payment.methods', ['plan' => $plan, 'amount' => $amount, 'currency' => $currency]) }}" 
               class="inline-flex items-center text-slate-500 hover:text-white transition-colors text-xs font-black uppercase tracking-widest group">
                <i class="fas fa-arrow-left mr-3 transition-transform group-hover:-translate-x-1"></i>
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
    button.innerHTML = '<i class="fas fa-check mr-2"></i> Copied!';
    button.classList.add('bg-green-600', 'shadow-green-500/20');
    button.classList.remove('bg-blue-600', 'shadow-blue-500/20');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('bg-green-600', 'shadow-green-500/20');
        button.classList.add('bg-blue-600', 'shadow-blue-500/20');
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

function confirmBankTransfer() {
    console.log('Confirming Bank Transfer');
    
    if (!isAuthenticated) {
        alert('Please login to continue with payment');
        window.location.href = loginUrl + '?redirect=' + encodeURIComponent(window.location.href);
        return;
    }
    
    // In a real app, this might show a file upload modal or a success message
    // For now, we'll initialize the payment record and show instructions
    const form = document.getElementById('paymentForm');
    const methodInput = document.getElementById('paymentMethod');
    
    if (form && methodInput) {
        methodInput.value = 'bank_transfer';
        alert('Transmission notice received! Please ensure you have sent the exact amount. Your premium access will be activated upon verification of the transfer.');
        form.submit();
    }
}
</script>
@endsection
