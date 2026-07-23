

<?php $__env->startSection('title', 'Forgot Password - Football Predictions'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-[calc(100vh-80px)] flex flex-col lg:flex-row bg-slate-50">
    <!-- Left Side: Visual/Branding (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-600 to-primary-900 items-center justify-center p-12 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
        </div>
        <div class="relative z-10 max-w-lg" data-aos="fade-right">
            <div class="mb-8">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl flex items-center justify-center mb-6 ring-1 ring-white/30">
                    <i class="fas fa-key text-3xl"></i>
                </div>
                <h1 class="text-5xl font-extrabold mb-6 leading-tight">Recover Your <br><span class="text-primary-300">Account!</span></h1>
                <p class="text-xl text-primary-100 leading-relaxed mb-8">Don't lose your edge. Reset your password to get back to accessing our professional predictions and winning tips.</p>
            </div>
            <div class="space-y-4">
                <div class="flex items-center space-x-4 p-4 rounded-xl bg-white/10 backdrop-blur-md ring-1 ring-white/20">
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-shield-alt text-green-400"></i>
                    </div>
                    <span class="text-lg">Secure Account Recovery</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Forgot Password Form -->
    <div class="flex-1 flex items-center justify-center p-8 lg:p-16">
        <div class="max-w-md w-full" data-aos="fade-up">
            <div class="text-center lg:text-left mb-10">
                <h2 class="text-4xl font-extrabold text-slate-900 mb-3">Reset Password</h2>
                <p class="text-slate-500 text-lg">Enter your email address and we'll send you a link to reset your password.</p>
            </div>

            <?php if(session('status')): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-2xl text-green-700 text-sm font-medium flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            <form class="space-y-6" method="POST" action="<?php echo e(route('password.email')); ?>">
                <?php echo csrf_field(); ?>
                
                <div class="space-y-5">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2 ml-1">Email Address</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                   class="block w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   placeholder="Enter your email" value="<?php echo e(old('email')); ?>">
                        </div>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600 flex items-center italic">
                                <i class="fas fa-exclamation-circle mr-1"></i> <?php echo e($message); ?>

                            </p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full flex justify-center items-center py-4 px-6 border border-transparent text-base font-bold rounded-2xl text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-500/30 transform transition-all active:scale-[0.98] shadow-lg shadow-primary-500/25">
                    <i class="fas fa-paper-plane mr-2"></i> Send Reset Link
                </button>

                <div class="text-center mt-6">
                    <a href="<?php echo e(route('login')); ?>" class="text-sm font-medium text-slate-500 hover:text-primary-600 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/simeonuba/Downloads/public_html (1)/resources/views/auth/forgot-password.blade.php ENDPATH**/ ?>