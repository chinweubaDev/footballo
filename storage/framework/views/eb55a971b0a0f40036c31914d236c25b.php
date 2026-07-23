

<?php $__env->startSection('title', 'Choose Payment Method - Football Predictions'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-slate-950 min-h-screen pb-20">
    <!-- Payment Hero -->
    <section class="relative bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 pt-24 pb-16 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, #3b82f6 1px, transparent 0); background-size: 32px 32px;"></div>
        </div>
        <div class="absolute -top-24 -right-24 w-[500px] h-[500px] bg-blue-500/10 blur-[120px] rounded-full"></div>
        
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-black uppercase tracking-[0.2em] mb-8" data-aos="fade-down">
                <i class="fas fa-lock mr-2"></i> Secure Checkout
            </div>
            <h1 class="text-4xl lg:text-5xl font-black text-white mb-6 tracking-tight" data-aos="fade-up">
                Finalize Your <span class="text-blue-500">Subscription</span>
            </h1>
            <p class="text-slate-400 max-w-xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                You're just one step away from unlocking premium expert analysis and high-confidence winning selections.
            </p>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10" data-plan="<?php echo e($plan); ?>" data-amount="<?php echo e($amount); ?>" data-currency="<?php echo e($currency); ?>">
        <!-- Order Summary Card -->
        <div class="bg-slate-900/50 backdrop-blur-xl rounded-[2.5rem] p-8 border border-white/10 mb-12 shadow-2xl" data-aos="fade-up">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex-1 w-full text-center md:text-left">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/5 text-slate-400 text-[10px] font-black uppercase tracking-widest mb-4">
                        Order Summary
                    </div>
                    <h2 class="text-2xl font-black text-white mb-6">Plan Details</h2>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center border border-blue-500/20">
                                <i class="fas fa-award text-blue-500"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Selected Plan</p>
                                <p class="text-lg font-bold text-white leading-tight"><?php echo e($pricingPlan->name); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center border border-white/5">
                                <i class="fas fa-clock text-slate-400 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Duration</p>
                                <p class="text-lg font-bold text-white leading-tight"><?php echo e($pricingPlan->duration_days); ?> Days Unlimited Access</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-slate-800/50 rounded-[2rem] p-8 border border-white/5 text-center min-w-[240px]">
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Total Amount</p>
                    <div class="flex items-baseline justify-center gap-1.5 mb-2">
                        <span class="text-xl font-bold text-blue-500"><?php echo e($currency); ?></span>
                        <span class="text-5xl font-black text-white tracking-tighter"><?php echo e(number_format($amount)); ?></span>
                    </div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">No Auto-Renewal</p>
                    <div class="p-3 bg-blue-500/10 rounded-xl border border-blue-500/10">
                        <span class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Premium Activation</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php
                $traditionalMethods = $paymentMethods->where('type', '!=', 'crypto');
                $cryptoMethods = $paymentMethods->where('type', 'crypto');
            ?>
            
            <!-- Traditional Payment Methods -->
            <?php if($traditionalMethods->count() > 0): ?>
            <div class="space-y-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center border border-blue-500/20">
                        <i class="fas fa-credit-card text-blue-500 text-xs"></i>
                    </div>
                    <h3 class="text-lg font-black text-white uppercase tracking-tighter">Instant Payment</h3>
                </div>
                
                <?php $__currentLoopData = $traditionalMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-slate-900/30 backdrop-blur-md rounded-3xl p-6 border border-white/5 hover:border-<?php echo e($method->color); ?>-500/30 transition-all duration-300 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="w-14 h-14 bg-<?php echo e($method->color); ?>-500/10 rounded-2xl flex items-center justify-center mr-5 border border-<?php echo e($method->color); ?>-500/20 group-hover:bg-<?php echo e($method->color); ?>-500 group-hover:text-white transition-all duration-300">
                                <i class="<?php echo e($method->icon); ?> text-<?php echo e($method->color); ?>-500 text-2xl group-hover:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-white font-black uppercase tracking-tight"><?php echo e($method->display_name); ?></h4>
                                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Instant Activation</p>
                            </div>
                        </div>
                        <a href="<?php echo e(route('payment.details', ['plan' => $plan, 'amount' => $amount, 'currency' => $currency, 'payment_method' => $method->type])); ?>" 
                           class="py-3 px-6 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-<?php echo e($method->color); ?>-600 transition-colors">
                            Choose
                        </a>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>

            <!-- Cryptocurrency Payment Methods -->
            <?php if($cryptoMethods->count() > 0): ?>
            <div class="space-y-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center border border-orange-500/20">
                        <i class="fab fa-bitcoin text-orange-500 text-xs"></i>
                    </div>
                    <h3 class="text-lg font-black text-white uppercase tracking-tighter">Crypto Assets</h3>
                </div>
                
                <?php $__currentLoopData = $cryptoMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-slate-900/30 backdrop-blur-md rounded-3xl p-6 border border-white/5 hover:border-<?php echo e($method->color); ?>-500/30 transition-all duration-300 group">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <div class="w-14 h-14 bg-<?php echo e($method->color); ?>-500/10 rounded-2xl flex items-center justify-center mr-5 border border-<?php echo e($method->color); ?>-500/20 group-hover:bg-<?php echo e($method->color); ?>-500 group-hover:text-white transition-all duration-300">
                                <i class="<?php echo e($method->icon); ?> text-<?php echo e($method->color); ?>-500 text-2xl group-hover:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-white font-black uppercase tracking-tight"><?php echo e($method->display_name); ?></h4>
                                <p class="text-[11px] text-slate-500 font-bold uppercase tracking-widest mt-0.5"><?php echo e(strtoupper($method->config['network'] ?? 'Blockchain')); ?> Network</p>
                            </div>
                        </div>
                        <a href="<?php echo e(route('payment.details', ['plan' => $plan, 'amount' => $amount, 'currency' => $currency, 'payment_method' => 'crypto', 'crypto_type' => $method->crypto_type])); ?>" 
                           class="py-3 px-6 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-<?php echo e($method->color); ?>-600 transition-colors">
                            Pay
                        </a>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Security & Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12">
            <div class="bg-blue-500/5 border border-blue-500/10 rounded-3xl p-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-4"></i>
                    <div>
                        <h4 class="text-sm font-black text-white uppercase tracking-widest mb-2">Activation Info</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Premium access is usually activated within minutes after successful payment. For crypto, it may take up to 3 confirmations.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-500/5 border border-green-500/10 rounded-3xl p-6">
                <div class="flex items-start">
                    <i class="fas fa-shield-alt text-green-500 mt-1 mr-4"></i>
                    <div>
                        <h4 class="text-sm font-black text-white uppercase tracking-widest mb-2">Secure Processing</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            All transactions are encrypted with 256-bit SSL security. We do not store your sensitive financial data on our servers.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Pricing -->
        <div class="mt-12 text-center pb-20">
            <a href="<?php echo e(route('pricing')); ?>" class="inline-flex items-center text-slate-500 hover:text-white transition-colors text-xs font-black uppercase tracking-widest group">
                <i class="fas fa-arrow-left mr-3 transition-transform group-hover:-translate-x-1"></i>
                Back to Pricing Plans
            </a>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md hidden z-50 flex items-center justify-center">
    <div class="bg-slate-900 border border-white/10 p-10 rounded-[2.5rem] shadow-2xl text-center max-w-sm w-full mx-4">
        <div class="relative w-20 h-20 mx-auto mb-8">
            <div class="absolute inset-0 border-4 border-blue-500/20 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center text-blue-500">
                <i class="fas fa-shield-alt text-2xl"></i>
            </div>
        </div>
        <h3 class="text-xl font-black text-white mb-3 tracking-tight">Initializing Secure Payment</h3>
        <p class="text-sm text-slate-400">Please wait while we connect to our secure payment gateway...</p>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/simeonuba/Downloads/public_html (1)/resources/views/payment/methods.blade.php ENDPATH**/ ?>