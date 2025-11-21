@extends('layouts.app')

@section('title', 'Admin Dashboard - Football Predictions')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 rounded-lg shadow-xl p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Admin Dashboard</h1>
                    <p class="text-purple-100">Manage your football prediction platform</p>
                </div>
                <div class="text-right">
                    <div class="bg-purple-500 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-crown mr-2"></i>Administrator
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-crown text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Premium Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['premium_users'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-credit-card text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                        <p class="text-2xl font-semibold text-gray-900">NGN {{ number_format($stats['total_payments']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-futbol text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Fixtures</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_fixtures'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-orange-100 rounded-full p-3">
                        <i class="fas fa-star text-orange-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Today's Tips</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['today_tips'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-pink-100 rounded-full p-3">
                        <i class="fas fa-crown text-pink-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Featured Predictions</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['featured_predictions'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="bg-red-100 rounded-full p-3">
                        <i class="fas fa-clock text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Payments</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_payments'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.fixtures') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-150 ease-in-out">
                    <i class="fas fa-futbol text-blue-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">Manage Fixtures</h3>
                        <p class="text-sm text-gray-600">Add and manage match fixtures</p>
                    </div>
                </a>

                <a href="{{ route('admin.payments') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-150 ease-in-out">
                    <i class="fas fa-credit-card text-green-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">View Payments</h3>
                        <p class="text-sm text-gray-600">Track user payments and subscriptions</p>
                    </div>
                </a>

                <a href="{{ route('admin.users') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-150 ease-in-out">
                    <i class="fas fa-users text-purple-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">Manage Users</h3>
                        <p class="text-sm text-gray-600">View and manage user accounts</p>
                    </div>
                </a>

                <a href="{{ route('admin.user-subscriptions') }}" class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition duration-150 ease-in-out">
                    <i class="fas fa-crown text-indigo-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">User Subscriptions</h3>
                        <p class="text-sm text-gray-600">Manage VIP/VVIP subscriptions</p>
                    </div>
                </a>

                <a href="{{ route('admin.results') }}" class="flex items-center p-4 bg-teal-50 rounded-lg hover:bg-teal-100 transition duration-150 ease-in-out">
                    <i class="fas fa-chart-line text-teal-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">VIP/VVIP Results</h3>
                        <p class="text-sm text-gray-600">Manage results displayed on home</p>
                    </div>
                </a>

                <button onclick="showAddFixtureModal()" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition duration-150 ease-in-out">
                    <i class="fas fa-plus text-yellow-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="font-semibold text-gray-900">Add Fixture</h3>
                        <p class="text-sm text-gray-600">Quickly add a new fixture</p>
                    </div>
                </button>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Fixtures</h2>
                <div class="space-y-4">
                    <div class="text-center py-8">
                        <i class="fas fa-futbol text-gray-300 text-4xl mb-4"></i>
                        <p class="text-gray-500">No fixtures added yet</p>
                        <a href="{{ route('admin.fixtures') }}" class="text-green-600 hover:text-green-700 text-sm font-medium">
                            Add your first fixture
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">System Status</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">API Football Connection</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>Connected
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Payment Gateway</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>Active
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Email Service</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Not Configured
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Database</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>Healthy
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Fixture Modal -->
<div id="addFixtureModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Fixture</h3>
                <button onclick="hideAddFixtureModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addFixtureForm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Country</label>
                        <select id="country" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <option value="">Select Country</option>
                            <option value="England">England</option>
                            <option value="Spain">Spain</option>
                            <option value="Germany">Germany</option>
                            <option value="Italy">Italy</option>
                            <option value="France">France</option>
                            <option value="Nigeria">Nigeria</option>
                            <option value="Ghana">Ghana</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">League</label>
                        <select id="league" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <option value="">Select League</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Season</label>
                        <select id="season" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">From Date</label>
                        <input type="date" id="from_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">To Date</label>
                        <input type="date" id="to_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="hideAddFixtureModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-150 ease-in-out">
                        Cancel
                    </button>
                    <button type="button" onclick="fetchFixtures()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-150 ease-in-out">
                        Fetch Fixtures
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Admin dashboard JavaScript is now handled by app.js -->
@endsection
