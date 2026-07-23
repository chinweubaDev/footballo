

<?php $__env->startSection('title', 'Register - Football Predictions'); ?>

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
                <h1 class="text-5xl font-extrabold mb-6 leading-tight">Start Your <br><span class="text-primary-300">Winning Streak</span></h1>
                <p class="text-xl text-primary-100 leading-relaxed mb-8">Join the community of expert bettors. Get access to premium predictions, real-time analysis, and daily winning tips.</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-white/10 backdrop-blur-md ring-1 ring-white/20">
                    <div class="text-2xl font-bold text-white mb-1">10k+</div>
                    <div class="text-sm text-primary-100">Happy Users</div>
                </div>
                <div class="p-4 rounded-xl bg-white/10 backdrop-blur-md ring-1 ring-white/20">
                    <div class="text-2xl font-bold text-white mb-1">85%</div>
                    <div class="text-sm text-primary-100">Success Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Registration Form -->
    <div class="flex-1 flex items-center justify-center p-8 lg:p-16">
        <div class="max-w-md w-full" data-aos="fade-up">
            <div class="text-center lg:text-left mb-8">
                <h2 class="text-4xl font-extrabold text-slate-900 mb-2">Create Account</h2>
                <p class="text-slate-500 text-lg">Already have an account? 
                    <a href="<?php echo e(route('login')); ?>" class="text-primary-600 font-semibold hover:underline">Sign In</a>
                </p>
            </div>

            <form class="space-y-4" method="POST" action="<?php echo e(route('register')); ?>">
                <?php echo csrf_field(); ?>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Full Name</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="name" name="name" type="text" autocomplete="name" required 
                                   class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   placeholder="Your Name" value="<?php echo e(old('name')); ?>">
                        </div>
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 italic"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Email Address</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                   class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   placeholder="Email@example.com" value="<?php echo e(old('email')); ?>">
                        </div>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-xs text-red-600 italic"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Phone (Optional)</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                                </div>
                                <input id="phone" name="phone" type="tel" autocomplete="tel" 
                                       class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       placeholder="Phone" value="<?php echo e(old('phone')); ?>">
                            </div>
                        </div>

                        <!-- Country -->
                        <div>
                            <label for="country" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Country</label>
                            <select id="country" name="country" 
                                    class="block w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">Select</option>
                                <option value="Nigeria" <?php echo e(old('country') == 'Nigeria' ? 'selected' : ''); ?>>Nigeria</option>
                                <option value="Ghana" <?php echo e(old('country') == 'Ghana' ? 'selected' : ''); ?>>Ghana</option>
                                <option value="Kenya" <?php echo e(old('country') == 'Kenya' ? 'selected' : ''); ?>>Kenya</option>
                                <option value="South Africa" <?php echo e(old('country') == 'South Africa' ? 'selected' : ''); ?>>South Africa</option>
                                <option value="United States" <?php echo e(old('country') == 'United States' ? 'selected' : ''); ?>>United States</option>
                                <option value="United Kingdom" <?php echo e(old('country') == 'United Kingdom' ? 'selected' : ''); ?>>United Kingdom</option>
                            </select>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="new-password" required 
                                   class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5 ml-1">Confirm Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-shield-alt text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                                   class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all" 
                                   placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full flex justify-center items-center py-4 px-6 border border-transparent text-base font-bold rounded-2xl text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-500/30 transform transition-all active:scale-[0.98] shadow-lg shadow-primary-500/25">
                        <i class="fas fa-user-plus mr-2"></i> Join Premium Today
                    </button>
                </div>

                <div class="text-center mt-6">
                    <p class="text-xs text-slate-500 leading-relaxed px-4">
                        By signing up, you agree to our 
                        <a href="#" class="text-primary-600 hover:underline">Terms of Service</a> & 
                        <a href="#" class="text-primary-600 hover:underline">Privacy Policy</a>
                    </p>
                </div>

                <?php if($errors->any()): ?>
                    <div class="p-3 bg-red-50 border border-red-100 rounded-2xl mt-4">
                        <ul class="space-y-0.5">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="text-xs text-red-700 flex items-center">
                                    <span class="w-1 h-1 bg-red-400 rounded-full mr-2"></span>
                                    <?php echo e($error); ?>

                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/simeonuba/Downloads/public_html (1)/resources/views/auth/register.blade.php ENDPATH**/ ?>