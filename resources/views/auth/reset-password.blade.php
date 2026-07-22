@extends('layouts.app')

@section('title', 'Reset Password - Football Predictions')

@section('content')
<div class="min-h-[calc(100vh-80px)] flex flex-col lg:flex-row bg-slate-50">
    <!-- Left Side: Visual/Branding (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-600 to-primary-900 items-center justify-center p-12 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
        </div>
        <div class="relative z-10 max-w-lg" data-aos="fade-right">
            <div class="mb-8">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl flex items-center justify-center mb-6 ring-1 ring-white/30">
                    <i class="fas fa-lock-open text-3xl"></i>
                </div>
                <h1 class="text-5xl font-extrabold mb-6 leading-tight">Create New <br><span class="text-primary-300">Password!</span></h1>
                <p class="text-xl text-primary-100 leading-relaxed mb-8">Secure your account with a strong password to continue enjoying your premium football tips and analysis.</p>
            </div>
            <div class="space-y-4">
                <div class="flex items-center space-x-4 p-4 rounded-xl bg-white/10 backdrop-blur-md ring-1 ring-white/20">
                    <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-key text-blue-400"></i>
                    </div>
                    <span class="text-lg">Strong Encryption Guaranteed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Reset Password Form -->
    <div class="flex-1 flex items-center justify-center p-8 lg:p-16">
        <div class="max-w-md w-full" data-aos="fade-up">
            <div class="text-center lg:text-left mb-10">
                <h2 class="text-4xl font-extrabold text-slate-900 mb-3">Set New Password</h2>
                <p class="text-slate-500 text-lg">Please enter your new password below to complete the account recovery process.</p>
            </div>

            <form class="space-y-6" method="POST" action="{{ route('password.update') }}">
                @csrf
                
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-5">
                    <!-- Email Field (Readonly often better, but Laravel validates it) -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2 ml-1">Email Address</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required readonly
                                   class="block w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-500 cursor-not-allowed focus:outline-none" 
                                   value="{{ $email ?? old('email') }}">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center italic">
                                <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2 ml-1">New Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="new-password" required 
                                   class="block w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all @error('password') border-red-500 @enderror" 
                                   placeholder="••••••••">
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center italic">
                                <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2 ml-1">Confirm New Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-shield-alt text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                            </div>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                                   class="block w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all" 
                                   placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full flex justify-center items-center py-4 px-6 border border-transparent text-base font-bold rounded-2xl text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-500/30 transform transition-all active:scale-[0.98] shadow-lg shadow-primary-500/25">
                    <i class="fas fa-save mr-2"></i> Update Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
