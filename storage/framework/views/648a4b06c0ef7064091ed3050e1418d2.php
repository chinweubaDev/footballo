<?php $__env->startSection('title', 'Pricing Plans - Football Predictions'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-slate-950 min-h-screen pb-20">
    <!-- Pricing Hero -->
    <section class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 pt-24 pb-20 overflow-hidden">
        <!-- Background effects -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #3b82f6 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="absolute -top-24 -right-24 w-[500px] h-[500px] bg-blue-500/10 blur-[120px] rounded-full animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-primary-500/5 blur-[100px] rounded-full translate-y-1/2"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-black uppercase tracking-[0.2em] mb-8" data-aos="fade-down">
                <i class="fas fa-gem mr-2"></i> Premium Access
            </div>
            <h1 class="text-5xl lg:text-7xl font-black text-white mb-6 leading-tight" data-aos="fade-up">
                Elevate Your <br>
                <span class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 bg-clip-text text-transparent italic">Winning Strategy</span>
            </h1>
            <p class="text-xl text-slate-400 mb-10 leading-relaxed max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Choose a plan that fits your betting style. Get access to expert analysis, high-confidence tips, and exclusive VIP markets.
            </p>

            <!-- Country Selection -->
            <div class="flex flex-col items-center" data-aos="fade-up" data-aos-delay="200">
                <div class="relative group">
                    <label for="countrySelect" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 text-center">Select Your Location</label>
                    <div class="relative">
                        <select id="countrySelect" class="appearance-none bg-slate-900/50 border border-white/10 text-white text-sm font-bold rounded-2xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-6 pr-12 py-4 backdrop-blur-xl transition-all hover:bg-slate-800/80 cursor-pointer">
                            <?php $__currentLoopData = $availableCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e($selectedCountry === $key ? 'selected' : ''); ?> class="bg-slate-900"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-500">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 inline-flex items-center px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-slate-400 text-xs font-bold">
                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                    Displaying prices in <span id="currentCountry" class="text-white ml-1"><?php echo e($selectedCountry); ?></span>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10">

                <!-- VIP Plans Section -->
        <div class="mb-20">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-black text-white mb-4 uppercase tracking-tighter">VIP Plans</h2>
                <div class="w-20 h-1 bg-blue-600 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php $__currentLoopData = $pricingPlans->filter(fn($plan) => Str::startsWith($plan->key, 'vip_')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $features = is_string($plan->features) ? json_decode($plan->features, true) : $plan->features;
                    $isPopular = $plan->key === 'vip_1_month';
                ?>

                <div id="plan-<?php echo e($plan->key); ?>" 
                     class="group relative bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] p-8 border <?php echo e($isPopular ? 'border-blue-500/50 shadow-[0_0_40px_rgba(59,130,246,0.1)]' : 'border-white/5'); ?> hover:border-blue-500/30 transition-all duration-500 <?php echo e($isPopular ? 'md:scale-105 z-10' : ''); ?>"
                     data-aos="fade-up" data-aos-delay="<?php echo e($loop->index * 100); ?>">
                     
                    <?php if($isPopular): ?>
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-gradient-to-r from-blue-500 to-blue-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-lg">Most Popular</span>
                    </div>
                    <?php endif; ?>

                    <div class="text-center mb-8">
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-500/10 text-blue-500 text-[10px] font-black uppercase tracking-widest mb-4">
                            VIP Standard
                        </div>
                        <h3 class="text-xl font-black text-white mb-4">
                            <?php echo e($plan->name); ?>

                        </h3>
                        <div class="flex items-baseline justify-center gap-1 mb-2" id="price-<?php echo e($plan->key); ?>">
                            <span class="text-lg font-bold text-slate-500 currency-<?php echo e($plan->key); ?>"><?php echo e($plan->getCurrencyForCountry($selectedCountry)); ?></span>
                            <span class="text-5xl font-black text-white amount-<?php echo e($plan->key); ?> tracking-tighter"><?php echo e(number_format($plan->getPriceForCountry($selectedCountry))); ?></span>
                        </div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                            <?php echo e($plan->duration_days); ?> Days Unlimited access
                        </p>
                    </div>

                    <div class="space-y-4 mb-10">
                        <?php if(!empty($features)): ?>
                            <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center space-x-3 text-slate-400 group-hover:text-slate-300 transition-colors">
                                <div class="w-5 h-5 rounded-full bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-[10px] text-blue-500"></i>
                                </div>
                                <span class="text-sm font-medium"><?php echo e($feature); ?></span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <p class="text-sm text-slate-600 italic text-center">Standard VIP benefits apply</p>
                        <?php endif; ?>
                    </div>

                    <a href="<?php echo e(route('payment.methods', [
                        'plan' => $plan->key,
                        'amount' => $plan->getPriceForCountry($selectedCountry),
                        'currency' => $plan->getCurrencyForCountry($selectedCountry)
                    ])); ?>" 
                       class="w-full py-4 rounded-2xl <?php echo e($isPopular ? 'bg-blue-600 hover:bg-blue-500' : 'bg-slate-800 hover:bg-slate-700'); ?> text-white font-black text-sm uppercase tracking-[0.1em] transition-all duration-300 text-center block shadow-lg hover:shadow-blue-500/20 link-<?php echo e($plan->key); ?>">
                        Get Started <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </a>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- VVIP Plans Section -->
        <div class="mb-20">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-3xl font-black text-white mb-4 uppercase tracking-tighter">VVIP Elite Plans</h2>
                <div class="w-20 h-1 bg-purple-600 mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php $__currentLoopData = $pricingPlans->filter(fn($plan) => Str::startsWith($plan->key, 'vvip_')); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $features = is_string($plan->features) ? json_decode($plan->features, true) : $plan->features;
                    $isPopular = $plan->key === 'vvip_1_month';
                ?>

                <div id="plan-<?php echo e($plan->key); ?>" 
                     class="group relative bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] p-8 border <?php echo e($isPopular ? 'border-purple-500/50 shadow-[0_0_40px_rgba(168,85,247,0.1)]' : 'border-white/5'); ?> hover:border-purple-500/30 transition-all duration-500 <?php echo e($isPopular ? 'md:scale-105 z-10' : ''); ?>"
                     data-aos="fade-up" data-aos-delay="<?php echo e($loop->index * 100); ?>">
                     
                    <?php if($isPopular): ?>
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-gradient-to-r from-purple-500 to-purple-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-lg">Most Popular</span>
                    </div>
                    <?php endif; ?>

                    <div class="text-center mb-8">
                        <div class="inline-flex items-center px-3 py-1 rounded-full bg-purple-500/10 text-purple-400 text-[10px] font-black uppercase tracking-widest mb-4">
                            VVIP Elite Access
                        </div>
                        <h3 class="text-xl font-black text-white mb-4">
                            <?php echo e($plan->name); ?>

                        </h3>
                        <div class="flex items-baseline justify-center gap-1 mb-2" id="price-<?php echo e($plan->key); ?>">
                            <span class="text-lg font-bold text-slate-500 currency-<?php echo e($plan->key); ?>"><?php echo e($plan->getCurrencyForCountry($selectedCountry)); ?></span>
                            <span class="text-5xl font-black text-white amount-<?php echo e($plan->key); ?> tracking-tighter"><?php echo e(number_format($plan->getPriceForCountry($selectedCountry))); ?></span>
                        </div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">
                            <?php echo e($plan->duration_days); ?> Days Unlimited access
                        </p>
                    </div>

                    <div class="space-y-4 mb-10">
                        <?php if(!empty($features)): ?>
                            <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center space-x-3 text-slate-400 group-hover:text-slate-300 transition-colors">
                                <div class="w-5 h-5 rounded-full bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-crown text-[10px] text-purple-500"></i>
                                </div>
                                <span class="text-sm font-medium"><?php echo e($feature); ?></span>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <p class="text-sm text-slate-600 italic text-center">Elite VVIP benefits apply</p>
                        <?php endif; ?>
                    </div>

                    <a href="<?php echo e(route('payment.methods', [
                        'plan' => $plan->key,
                        'amount' => $plan->getPriceForCountry($selectedCountry),
                        'currency' => $plan->getCurrencyForCountry($selectedCountry)
                    ])); ?>" 
                       class="w-full py-4 rounded-2xl <?php echo e($isPopular ? 'bg-purple-600 hover:bg-purple-500' : 'bg-slate-800 hover:bg-slate-700'); ?> text-white font-black text-sm uppercase tracking-[0.1em] transition-all duration-300 text-center block shadow-lg hover:shadow-purple-500/20 link-<?php echo e($plan->key); ?>">
                        Get Started <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                    </a>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>




        <!-- Features Section -->
        <div class="bg-slate-900/50 backdrop-blur-xl rounded-[3rem] p-12 border border-white/5 mb-20" data-aos="fade-up">
            <h2 class="text-3xl font-black text-white text-center mb-16 uppercase tracking-widest">Why Choose Our Premium Plans</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="text-center group">
                    <div class="w-16 h-16 bg-blue-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-blue-500/20 group-hover:bg-blue-500 transition-all duration-300">
                        <i class="fas fa-chart-line text-blue-500 text-2xl group-hover:text-white"></i>
                    </div>
                    <h3 class="text-lg font-black text-white mb-3 uppercase tracking-tighter">Precision Analysis</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Our proprietary algorithms analyze over 1,000 data points per match to ensure maximum accuracy.</p>
                </div>
                <div class="text-center group">
                    <div class="w-16 h-16 bg-green-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-green-500/20 group-hover:bg-green-500 transition-all duration-300">
                        <i class="fas fa-bullseye text-green-500 text-2xl group-hover:text-white"></i>
                    </div>
                    <h3 class="text-lg font-black text-white mb-3 uppercase tracking-tighter">High Win Rate</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Historically verified results with a consistent track record of success across all leagues.</p>
                </div>
                <div class="text-center group">
                    <div class="w-16 h-16 bg-purple-500/10 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-purple-500/20 group-hover:bg-purple-500 transition-all duration-300">
                        <i class="fas fa-clock text-purple-500 text-2xl group-hover:text-white"></i>
                    </div>
                    <h3 class="text-lg font-black text-white mb-3 uppercase tracking-tighter">Instant Alerts</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">Never miss a winning pick. Receive instant notifications the moment our experts lock in a selection.</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="max-w-4xl mx-auto" data-aos="fade-up">
            <h2 class="text-3xl font-black text-white text-center mb-12 uppercase tracking-widest">Payment & Support</h2>
            <div class="space-y-4">
                <div class="bg-slate-900/30 border border-white/5 rounded-2xl p-6 hover:bg-slate-900/50 transition-all">
                    <h3 class="text-lg font-bold text-white mb-3 flex items-center">
                        <i class="fas fa-wallet text-blue-500 mr-3 opacity-70"></i>
                        Available Payment Methods
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                        <div class="text-xs font-bold text-slate-500 py-2 px-3 bg-white/5 rounded-lg border border-white/5 text-center">Flutterwave</div>
                        <div class="text-xs font-bold text-slate-500 py-2 px-3 bg-white/5 rounded-lg border border-white/5 text-center">PayPal</div>
                        <div class="text-xs font-bold text-slate-500 py-2 px-3 bg-white/5 rounded-lg border border-white/5 text-center">Skrill</div>
                        <div class="text-xs font-bold text-slate-500 py-2 px-3 bg-white/5 rounded-lg border border-white/5 text-center">Crypto</div>
                    </div>
                </div>
                
                <div class="bg-slate-900/30 border border-white/5 rounded-2xl p-6 hover:bg-slate-900/50 transition-all">
                    <h3 class="text-lg font-bold text-white mb-3 flex items-center">
                        <i class="fas fa-shield-alt text-green-500 mr-3 opacity-70"></i>
                        Refund Policy
                    </h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        We offer a 7-day money-back guarantee for all new institutional and individual subscribers. If you're not satisfied, our support team is available 24/7 to assist.
                    </p>
                </div>

                <div class="bg-slate-900/30 border border-white/5 rounded-2xl p-6 hover:bg-slate-900/50 transition-all">
                    <h3 class="text-lg font-bold text-white mb-3 flex items-center">
                        <i class="fas fa-undo text-red-500 mr-3 opacity-70"></i>
                        Subscription Cancellation
                    </h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        You can cancel your subscription at any time within your dashboard settings. You will continue to have access until the end of your current billing cycle.
                    </p>
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
    pricingCards.forEach(card => {
        card.classList.add('opacity-40', 'pointer-events-none');
    });

    // Update current country display
    document.getElementById('currentCountry').textContent = country;

    // Fetch new pricing
    fetch(`/pricing/country?country=${country}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Object.keys(data.pricing).forEach(planKey => {
                    const plan = data.pricing[planKey];
                    
                    // Update currency
                    const currencyElement = document.querySelector(`.currency-${planKey}`);
                    if (currencyElement) {
                        currencyElement.textContent = plan.currency;
                    }
                    
                    // Update amount
                    const amountElement = document.querySelector(`.amount-${planKey}`);
                    if (amountElement) {
                        amountElement.textContent = plan.price.toLocaleString();
                    }
                    
                    // Update the link
                    const link = document.querySelector(`.link-${planKey}`);
                    if (link) {
                        const url = new URL(link.href);
                        url.searchParams.set('amount', plan.price);
                        url.searchParams.set('currency', plan.currency);
                        link.href = url.toString();
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error updating pricing:', error);
        })
        .finally(() => {
            // Remove loading state
            pricingCards.forEach(card => {
                card.classList.remove('opacity-40', 'pointer-events-none');
            });
        });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/simeonuba/Downloads/public_html (1)/resources/views/pricing/index.blade.php ENDPATH**/ ?>