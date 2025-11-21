@extends('layouts.app')

@section('title', 'Profile - Football Predictions')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center">
                <div class="bg-green-100 rounded-full p-4">
                    <i class="fas fa-user text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
                    <p class="text-gray-600">Manage your account information and preferences</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Personal Information</h2>
                    
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('phone') border-red-500 @enderror">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                                <select name="country" id="country" 
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('country') border-red-500 @enderror">
                                    <option value="">Select your country</option>
                                    <option value="Nigeria" {{ old('country', $user->country) == 'Nigeria' ? 'selected' : '' }}>Nigeria</option>
                                    <option value="Ghana" {{ old('country', $user->country) == 'Ghana' ? 'selected' : '' }}>Ghana</option>
                                    <option value="Kenya" {{ old('country', $user->country) == 'Kenya' ? 'selected' : '' }}>Kenya</option>
                                    <option value="South Africa" {{ old('country', $user->country) == 'South Africa' ? 'selected' : '' }}>South Africa</option>
                                    <option value="United States" {{ old('country', $user->country) == 'United States' ? 'selected' : '' }}>United States</option>
                                    <option value="United Kingdom" {{ old('country', $user->country) == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    <option value="Other" {{ old('country', $user->country) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('country')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out font-semibold">
                                    Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Change Password</h2>
                    
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                <input type="password" name="current_password" id="current_password" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('current_password') border-red-500 @enderror">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input type="password" name="password" id="password" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('password') border-red-500 @enderror">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out font-semibold">
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Info Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Account Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Member Since</label>
                            <p class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Account Status</label>
                            <div class="flex items-center mt-1">
                                @if($user->hasActiveVVIP())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-gem mr-1"></i>VVIP
                                    </span>
                                @elseif($user->hasActiveVIP())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-crown mr-1"></i>VIP
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-user mr-1"></i>Free
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($user->hasActiveVVIP())
                        <div>
                            <label class="block text-sm font-medium text-gray-500">VVIP Expires</label>
                            <p class="text-sm text-gray-900">{{ $user->vvip_expires_at->format('M d, Y') }}</p>
                        </div>
                        @elseif($user->hasActiveVIP())
                        <div>
                            <label class="block text-sm font-medium text-gray-500">VIP Expires</label>
                            <p class="text-sm text-gray-900">{{ $user->vip_expires_at->format('M d, Y') }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email Verified</label>
                            <div class="flex items-center mt-1">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Not Verified
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!$user->hasActiveVIP() && !$user->hasActiveVVIP())
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upgrade to VIP/VVIP</h3>
                        <p class="text-sm text-gray-600 mb-4">Get access to exclusive predictions and expert analysis.</p>
                        <a href="{{ route('pricing') }}" 
                           class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out font-semibold text-center block">
                            View Plans
                        </a>
                    </div>
                    @elseif($user->hasActiveVIP() && !$user->hasActiveVVIP())
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upgrade to VVIP</h3>
                        <p class="text-sm text-gray-600 mb-4">Get access to our most exclusive predictions and analysis.</p>
                        <a href="{{ route('pricing') }}" 
                           class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition duration-150 ease-in-out font-semibold text-center block">
                            Upgrade to VVIP
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Quick Stats</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Predictions</span>
                            <span class="text-sm font-medium">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Winning Rate</span>
                            <span class="text-sm font-medium">0%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Spent</span>
                            <span class="text-sm font-medium">NGN 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Last Login</span>
                            <span class="text-sm font-medium">{{ now()->format('M d') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
