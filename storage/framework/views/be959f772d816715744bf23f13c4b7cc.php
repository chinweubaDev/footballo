

<?php $__env->startSection('title', 'Login - Football Predictions'); ?>

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
                    <i class="fas fa-futbol text-3xl"></i>
                </div>
                <h1 class="text-5xl font-extrabold mb-6 leading-tight">Welcome Back to <br><span class="text-primary-300">Winning!</span></h1>
                <p class="text-xl text-primary-100 leading-relaxed mb-8">Access your expert football predictions, VIP tips, and detailed match analysis. Your journey to better betting continues here.</p>
            </div>
            <div class="space-y-4">
                <div class="flex items-center space-x-4 p-4 rounded-xl bg-white/10 backdrop-blur-md ring-1 ring-white/20">
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-check text-green-400"></i>
                    </div>
                    <span class="text-lg">85%+ Accuracy on VIP Tips</span>
                </div>
                <div class="flex items-center space-x-4 p-4 rounded-xl bg-white/10 backdrop-blur-md ring-1 ring-white/20">
                    <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-bolt text-blue-400"></i>
                    </div>
                    <span class="text-lg">Real-time Match Updates</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="flex-1 flex items-center justify-center p-8 lg:p-16">
        <div class="max-w-md w-full" data-aos="fade-up">
            <div class="text-center lg:text-left mb-10">
                <h2 class="text-4xl font-extrabold text-slate-900 mb-3">Sign In</h2>
                <p class="text-slate-500 text-lg">New to Football Predictions? 
                    <a href="<?php echo e(route('register')); ?>" class="text-primary-600 font-semibold hover:underline">Create an account</a>
                </p>
            </div>

            <form class="space-y-6" method="POST" action="<?php echo e(route('login')); ?>">
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

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2 ml-1">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <a href="<?php echo e(route('password.request')); ?>" class="text-sm font-medium text-primary-600 hover:text-primary-700">Forgot password?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required 
                                   class="block w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   placeholder="••••••••">
                        </div>
                        <?php $__errorArgs = ['password'];
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

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" 
                           class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-slate-300 rounded-lg transition-all cursor-pointer">
                    <label for="remember" class="ml-3 block text-sm text-slate-600 cursor-pointer">
                        Keep me signed in for 30 days
                    </label>
                </div>

                <button type="submit" 
                        class="w-full flex justify-center items-center py-4 px-6 border border-transparent text-base font-bold rounded-2xl text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-500/30 transform transition-all active:scale-[0.98] shadow-lg shadow-primary-500/25">
                    <i class="fas fa-sign-in-alt mr-2"></i> Sign In to Account
                </button>

                <?php if($errors->any()): ?>
                    <div class="p-4 bg-red-50 border border-red-100 rounded-2xl" data-aos="shake">
                        <ul class="space-y-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="text-sm text-red-700 flex items-center">
                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full mr-2"></span>
                                    <?php echo e($error); ?>

                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </form>

            <div class="mt-10 pt-10 border-t border-slate-100">
                <p class="text-center text-slate-400 text-sm italic">
                    By signing in, you agree to our 
                    <a href="#" class="text-slate-600 hover:underline">Terms of Service</a> & 
                    <a href="#" class="text-slate-600 hover:underline">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/simeonuba/Downloads/public_html (1)/resources/views/auth/login.blade.php ENDPATH**/ ?>