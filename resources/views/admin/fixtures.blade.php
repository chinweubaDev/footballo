@extends('layouts.app')

@section('title', 'Manage Fixtures - Admin Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manage Fixtures</h1>
                    <p class="text-gray-600">Add, edit, and manage football fixtures and predictions</p>
                </div>
                <button onclick="showAddFixtureModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                    <i class="fas fa-plus mr-2"></i>Add Fixture
                </button>
            </div>
        </div>

        <!-- Fixtures Table -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">All Fixtures</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Match</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">League</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flags</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($fixtures as $fixture)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $fixture->home_team }} vs {{ $fixture->away_team }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $fixture->league_name }}</div>
                                <div class="text-sm text-gray-500">{{ $fixture->league_country }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $fixture->match_date->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $fixture->status === 'finished' ? 'bg-green-100 text-green-800' : 
                                       ($fixture->status === 'live' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($fixture->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-1">
                                    @if($fixture->today_tip)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-star mr-1"></i>Today
                                        </span>
                                    @endif
                                    @if($fixture->featured)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-crown mr-1"></i>Featured
                                        </span>
                                    @endif
                                    @if($fixture->maxodds_tip)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-chart-line mr-1"></i>Maxodds
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="editFixture({{ $fixture->id }})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteFixture({{ $fixture->id }})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-futbol text-4xl mb-4"></i>
                                    <p class="text-lg">No fixtures found</p>
                                    <p class="text-sm">Add your first fixture to get started</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($fixtures->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $fixtures->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Fixture Modal -->
<div id="addFixtureModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Fixture</h3>
                <button onclick="hideAddFixtureModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Step 1: Fetch Fixtures -->
            <div id="step1">
                <h4 class="text-md font-semibold text-gray-700 mb-4">Step 1: Fetch Fixtures from API</h4>
                <form id="fetchFixturesForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Country</label>
                            <div class="relative">
                            <select id="country" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                    <option value="">Loading countries...</option>
                            </select>
                                <div id="country-loading" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-green-600"></div>
                                </div>
                            </div>
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
                              
                                <option value="2025">2025</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" id="date" value="<?php echo date('Y-m-d'); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideAddFixtureModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-150 ease-in-out">
                            Cancel
                        </button>
                        <button type="button" onclick="fetchFixtures()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150 ease-in-out">
                            <i class="fas fa-search mr-2"></i>Fetch Fixtures
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Step 2: Select Fixture -->
            <div id="step2" class="hidden">
                <h4 class="text-md font-semibold text-gray-700 mb-4">Step 2: Select Fixture to Add</h4>
                <div id="fixturesList" class="max-h-96 overflow-y-auto border border-gray-200 rounded-md p-4">
                    <!-- Fixtures will be loaded here -->
                </div>
                <div class="mt-4 flex justify-end space-x-3">
                    <button type="button" onclick="showStep1()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-150 ease-in-out">
                        Back
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Add Prediction -->
            <div id="step3" class="hidden">
                <h4 class="text-md font-semibold text-gray-700 mb-4">Step 3: Add Prediction Details</h4>
                <form id="addPredictionForm">
                    <input type="hidden" id="selectedFixture" name="fixture_data">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                <option value="1X2">1X2</option>
                                <option value="Over/Under">Over/Under</option>
                                <option value="Both Teams to Score">Both Teams to Score</option>
                                <option value="Double Chance">Double Chance</option>
                                <option value="Correct Score">Correct Score</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tip</label>
                            <input type="text" name="tip" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="e.g., 1, X, 2, Over 2.5">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confidence (%)</label>
                            <input type="number" name="confidence" min="1" max="100" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="85">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Odds</label>
                            <input type="number" name="odds" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="2.50">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Analysis</label>
                        <textarea name="analysis" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Enter your analysis..."></textarea>
                    </div>
                    
                    <!-- Tip Categories Section -->
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tip Categories</h3>
                        <p class="text-sm text-gray-600 mb-4">Check categories and provide specific tips for each selected category</p>
                        
                        <!-- Today's Tip -->
                        <div class="border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="today_tip" id="today_tip" class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                                <label for="today_tip" class="ml-2 block text-sm font-semibold text-gray-900">
                                    <i class="fas fa-star text-yellow-500 mr-1"></i>Today's Tip
                                </label>
                            </div>
                            <div class="ml-6">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Today's Tip Content</label>
                                <textarea name="today_tip_content" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500 text-sm" placeholder="Enter specific tip content for today's tip..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Featured Tip -->
                        <div class="border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="featured" id="featured" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                <label for="featured" class="ml-2 block text-sm font-semibold text-gray-900">
                                    <i class="fas fa-crown text-purple-500 mr-1"></i>Featured
                                </label>
                            </div>
                            <div class="ml-6">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Featured Tip Content</label>
                                <textarea name="featured_tip_content" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="Enter specific tip content for featured tip..."></textarea>
                            </div>
                        </div>
                        
                        <!-- VIP Tip -->
                        <div class="border border-blue-200 rounded-lg p-4 mb-4 bg-blue-50">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="is_vip" id="is_vip" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_vip" class="ml-2 block text-sm font-semibold text-gray-900">
                                    <i class="fas fa-gem text-blue-600 mr-1"></i>VIP
                                </label>
                            </div>
                            <div class="ml-6">
                                <label class="block text-xs font-medium text-gray-700 mb-1">VIP Tip Content</label>
                                <textarea name="vip_tip_content" rows="2" class="block w-full px-3 py-2 border border-blue-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Enter exclusive VIP tip content..."></textarea>
                            </div>
                        </div>
                        
                        <!-- VVIP Tip -->
                        <div class="border border-purple-200 rounded-lg p-4 mb-4 bg-purple-50">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="is_vvip" id="is_vvip" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                <label for="is_vvip" class="ml-2 block text-sm font-semibold text-gray-900">
                                    <i class="fas fa-crown text-purple-600 mr-1"></i>VVIP
                                </label>
                            </div>
                            <div class="ml-6">
                                <label class="block text-xs font-medium text-gray-700 mb-1">VVIP Tip Content</label>
                                <textarea name="vvip_tip_content" rows="2" class="block w-full px-3 py-2 border border-purple-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 text-sm" placeholder="Enter exclusive VVIP tip content..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Sure Pick -->
                        <div class="border border-green-200 rounded-lg p-4 mb-4 bg-green-50">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="is_surepick" id="is_surepick" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <label for="is_surepick" class="ml-2 block text-sm font-semibold text-gray-900">
                                    <i class="fas fa-check-circle text-green-600 mr-1"></i>Sure Pick
                                </label>
                            </div>
                            <div class="ml-6">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Sure Pick Tip Content</label>
                                <textarea name="surepick_tip_content" rows="2" class="block w-full px-3 py-2 border border-green-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 text-sm" placeholder="Enter sure pick tip content..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Maxodds Tip -->
                        <div class="border border-gray-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" name="maxodds_tip" id="maxodds_tip" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="maxodds_tip" class="ml-2 block text-sm font-semibold text-gray-900">
                                    <i class="fas fa-chart-line text-blue-600 mr-1"></i>Maxodds Tip
                                </label>
                            </div>
                            <div class="ml-6">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Maxodds Tip Content</label>
                                <textarea name="maxodds_tip_content" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Enter maxodds tip content..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Premium Checkbox -->
                        <div class="flex items-center">
                            <input type="checkbox" name="is_premium" id="is_premium" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <label for="is_premium" class="ml-2 block text-sm text-gray-900">Mark as Premium</label>
                        </div>
                    </div>
                    
                    <!-- VIP/VVIP Tips Section -->
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">VIP/VVIP Tips</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tip Type</label>
                                <select name="tip_type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                    <option value="">Select Tip Type</option>
                                    <option value="vip">VIP Tip</option>
                                    <option value="vvip">VVIP Tip</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tip Title</label>
                                <input type="text" name="tip_title" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="e.g., High Confidence Home Win">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tip Content</label>
                            <textarea name="tip_content" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Enter detailed tip analysis..."></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Match Date</label>
                                <input type="date" name="match_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Match Time</label>
                                <input type="time" name="match_time" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div class="flex items-center mt-4">
                            <input type="checkbox" name="is_featured_tip" id="is_featured_tip" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            <label for="is_featured_tip" class="ml-2 block text-sm text-gray-900">Featured Tip</label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="showStep2()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-150 ease-in-out">
                            Back
                        </button>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-150 ease-in-out">
                            <i class="fas fa-save mr-2"></i>Add Fixture & Prediction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Fixture Modal -->
<div id="editFixtureModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Fixture</h3>
                <button onclick="hideEditFixtureModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editFixtureForm">
                <input type="hidden" id="editFixtureId" name="fixture_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Home Team</label>
                        <input type="text" id="editHomeTeam" name="home_team" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Away Team</label>
                        <input type="text" id="editAwayTeam" name="away_team" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">League Name</label>
                        <input type="text" id="editLeagueName" name="league_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Match Date</label>
                        <input type="datetime-local" id="editMatchDate" name="match_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="edit_today_tip" id="edit_today_tip" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="edit_today_tip" class="ml-2 block text-sm text-gray-900">Today's Tip</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="edit_featured" id="edit_featured" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="edit_featured" class="ml-2 block text-sm text-gray-900">Featured</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="edit_maxodds_tip" id="edit_maxodds_tip" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="edit_maxodds_tip" class="ml-2 block text-sm text-gray-900">Maxodds Tip</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="edit_is_vip" id="edit_is_vip" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="edit_is_vip" class="ml-2 block text-sm text-gray-900">VIP</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="edit_is_vvip" id="edit_is_vvip" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="edit_is_vvip" class="ml-2 block text-sm text-gray-900">VVIP</label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="edit_is_surepick" id="edit_is_surepick" class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                        <label for="edit_is_surepick" class="ml-2 block text-sm text-gray-900">Sure Pick</label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideEditFixtureModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition duration-150 ease-in-out">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150 ease-in-out">
                        <i class="fas fa-save mr-2"></i>Update Fixture
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Fetching Fixtures</h3>
            <p class="text-gray-600">Please wait while we fetch fixtures from the API...</p>
        </div>
    </div>
</div>

<script>
// Admin fixture management JavaScript
let currentStep = 1;
let fetchedFixtures = [];

// Loading overlay functions
function showLoadingOverlay() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoadingOverlay() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}


// Load countries when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, loading countries...');
    loadCountries();
    
    // Add event listener for fetch fixtures button as backup
    const fetchButton = document.querySelector('button[onclick="fetchFixtures()"]');
    if (fetchButton) {
        fetchButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Fetch button clicked via event listener');
            fetchFixtures();
        });
    }
});

// Load countries when modal opens
function showAddFixtureModal() {
    document.getElementById('addFixtureModal').classList.remove('hidden');
    // Countries are already loaded on page load, no need to reload
    resetModal();
}

function hideAddFixtureModal() {
    document.getElementById('addFixtureModal').classList.add('hidden');
    resetModal();
}

function resetModal() {
    currentStep = 1;
    showStep1();
    document.getElementById('fetchFixturesForm').reset();
    document.getElementById('addPredictionForm').reset();
    document.getElementById('fixturesList').innerHTML = '';
    fetchedFixtures = [];
}

function showStep1() {
    currentStep = 1;
    document.getElementById('step1').classList.remove('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.add('hidden');
}

function showStep2() {
    currentStep = 2;
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
    document.getElementById('step3').classList.add('hidden');
}

function showStep3() {
    currentStep = 3;
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.remove('hidden');
}

// Load countries from API
async function loadCountries() {
    console.log('Loading countries...');
    const countrySelect = document.getElementById('country');
    const loadingSpinner = document.getElementById('country-loading');
    
    // Show loading spinner
    if (loadingSpinner) {
        loadingSpinner.style.display = 'flex';
    }
    
    try {
        const response = await fetch('/admin/countries');
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.status === 'success') {
            countrySelect.innerHTML = '<option value="">Select Country</option>';
            
            if (data.countries && data.countries.length > 0) {
                data.countries.forEach(country => {
                    const option = document.createElement('option');
                    option.value = country.name;
                    option.textContent = country.name;
                    countrySelect.appendChild(option);
                });
                console.log('Countries loaded successfully:', data.countries.length);
            } else {
                console.warn('No countries returned from API');
                countrySelect.innerHTML = '<option value="">No countries available</option>';
            }
        } else {
            console.error('Failed to load countries:', data.message);
            console.error('Debug info:', data.debug);
            countrySelect.innerHTML = `<option value="">Error: ${data.message}</option>`;
        }
    } catch (error) {
        console.error('Error loading countries:', error);
        countrySelect.innerHTML = `<option value="">Error: ${error.message}</option>`;
    } finally {
        // Hide loading spinner
        if (loadingSpinner) {
            loadingSpinner.style.display = 'none';
        }
    }
}

// Load leagues when country is selected
document.getElementById('country').addEventListener('change', async function() {
    const country = this.value;
    const leagueSelect = document.getElementById('league');
    
    if (!country) {
        leagueSelect.innerHTML = '<option value="">Select League</option>';
        return;
    }
    
    try {
        const response = await fetch(`/admin/leagues?country=${encodeURIComponent(country)}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            leagueSelect.innerHTML = '<option value="">Select League</option>';
            
            data.leagues.forEach(league => {
                const option = document.createElement('option');
                option.value = league.league.id;
                option.textContent = league.league.name;
                leagueSelect.appendChild(option);
            });
        } else {
            console.error('Failed to load leagues:', data.message);
        }
    } catch (error) {
        console.error('Error loading leagues:', error);
    }
});

// Fetch fixtures from API
async function fetchFixtures() {
    console.log('Fetch fixtures function called');
    
    const country = document.getElementById('country').value;
    const league = document.getElementById('league').value;
    const season = document.getElementById('season').value;
    const date = document.getElementById('date').value;
    
    console.log('Form values:', { country, league, season, date });
    
    if (!country || !league || !season || !date) {
        alert('Please fill in all fields');
        return;
    }
    
    showLoadingOverlay();
    
    try {
        const url = `/admin/fixtures/fetch?country=${encodeURIComponent(country)}&league=${encodeURIComponent(league)}&season=${encodeURIComponent(season)}&date=${encodeURIComponent(date)}`;
        console.log('Fetching from URL:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.status === 'success') {
            fetchedFixtures = data.fixtures;
            displayFixtures(fetchedFixtures);
            showStep2();
        } else {
            alert('Failed to fetch fixtures: ' + data.message);
        }
    } catch (error) {
        console.error('Error fetching fixtures:', error);
        alert('Error fetching fixtures: ' + error.message);
    } finally {
        hideLoadingOverlay();
    }
}

// Display fetched fixtures
function displayFixtures(fixtures) {
    const fixturesList = document.getElementById('fixturesList');
    fixturesList.innerHTML = '';
    
    if (!fixtures || !Array.isArray(fixtures)) {
        fixturesList.innerHTML = '<p class="text-red-500 text-center py-4">Error: Invalid fixtures data received.</p>';
        return;
    }
    
    if (fixtures.length === 0) {
        fixturesList.innerHTML = '<p class="text-gray-500 text-center py-4">No fixtures found for the selected criteria.</p>';
        return;
    }
    
    fixtures.forEach((fixture, index) => {
        const fixtureDiv = document.createElement('div');
        fixtureDiv.className = 'border border-gray-200 rounded-lg p-4 mb-3 hover:bg-gray-50 cursor-pointer';
        fixtureDiv.onclick = () => selectFixture(fixture);
        
        // Check if fixture has the expected structure
        if (!fixture.fixture || !fixture.teams || !fixture.league) {
            fixtureDiv.innerHTML = `
                <div class="text-red-500">
                    <p>Invalid fixture data structure</p>
                </div>
            `;
            fixturesList.appendChild(fixtureDiv);
            return;
        }
        
        const matchDate = new Date(fixture.fixture.date);
        const formattedDate = matchDate.toLocaleDateString() + ' ' + matchDate.toLocaleTimeString();
        
        fixtureDiv.innerHTML = `
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="font-semibold text-gray-900">${fixture.teams.home.name} vs ${fixture.teams.away.name}</h4>
                    <p class="text-sm text-gray-600">${fixture.league.name} - ${fixture.league.country}</p>
                    <p class="text-sm text-gray-500">${formattedDate}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${fixture.fixture.status.long}
                    </span>
                </div>
            </div>
        `;
        
        fixturesList.appendChild(fixtureDiv);
    });
}

// Select a fixture and proceed to step 3
function selectFixture(fixture) {
    document.getElementById('selectedFixture').value = JSON.stringify(fixture);
    showStep3();
}

// Add fixture and prediction
document.getElementById('addPredictionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fixtureData = JSON.parse(document.getElementById('selectedFixture').value);
    
    const requestData = {
        api_fixture_id: fixtureData.fixture.id,
        league_name: fixtureData.league.name,
        league_country: fixtureData.league.country,
        league_id: fixtureData.league.id,
        season: fixtureData.league.season,
        home_team: fixtureData.teams.home.name,
        away_team: fixtureData.teams.away.name,
        home_team_id: fixtureData.teams.home.id,
        away_team_id: fixtureData.teams.away.id,
        home_team_logo: fixtureData.teams.home.logo,
        away_team_logo: fixtureData.teams.away.logo,
        league_logo: fixtureData.league.logo,
        league_flag: fixtureData.league.flag,
        match_date: fixtureData.fixture.date,
        category: formData.get('category'),
        tip: formData.get('tip'),
        confidence: formData.get('confidence'),
        odds: formData.get('odds'),
        analysis: formData.get('analysis'),
        today_tip: formData.get('today_tip') === 'on',
        featured: formData.get('featured') === 'on',
        maxodds_tip: formData.get('maxodds_tip') === 'on',
        is_premium: formData.get('is_premium') === 'on',
        is_vip: formData.get('is_vip') === 'on',
        is_vvip: formData.get('is_vvip') === 'on',
        is_surepick: formData.get('is_surepick') === 'on',
        today_tip_content: formData.get('today_tip_content'),
        featured_tip_content: formData.get('featured_tip_content'),
        vip_tip_content: formData.get('vip_tip_content'),
        vvip_tip_content: formData.get('vvip_tip_content'),
        surepick_tip_content: formData.get('surepick_tip_content'),
        maxodds_tip_content: formData.get('maxodds_tip_content')
    };
    
    try {
        const response = await fetch('/admin/fixtures/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert('Fixture and prediction added successfully!');
            hideAddFixtureModal();
            location.reload(); // Refresh the page to show the new fixture
        } else {
            alert('Failed to add fixture: ' + data.message);
        }
    } catch (error) {
        console.error('Error adding fixture:', error);
        alert('Error adding fixture. Please try again.');
    }
});

// Edit fixture functionality
async function editFixture(fixtureId) {
    try {
        const response = await fetch(`/admin/fixtures/${fixtureId}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const fixture = data.fixture;
            document.getElementById('editFixtureId').value = fixture.id;
            document.getElementById('editHomeTeam').value = fixture.home_team;
            document.getElementById('editAwayTeam').value = fixture.away_team;
            document.getElementById('editLeagueName').value = fixture.league_name;
            document.getElementById('editMatchDate').value = new Date(fixture.match_date).toISOString().slice(0, 16);
            document.getElementById('edit_today_tip').checked = fixture.today_tip;
            document.getElementById('edit_featured').checked = fixture.featured;
            document.getElementById('edit_maxodds_tip').checked = fixture.maxodds_tip;
            document.getElementById('edit_is_vip').checked = fixture.is_vip || false;
            document.getElementById('edit_is_vvip').checked = fixture.is_vvip || false;
            document.getElementById('edit_is_surepick').checked = fixture.is_surepick || false;
            
            document.getElementById('editFixtureModal').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading fixture:', error);
    }
}

function hideEditFixtureModal() {
    document.getElementById('editFixtureModal').classList.add('hidden');
}

// Update fixture
document.getElementById('editFixtureForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fixtureId = formData.get('fixture_id');
    
    const requestData = {
        home_team: formData.get('home_team'),
        away_team: formData.get('away_team'),
        league_name: formData.get('league_name'),
        match_date: formData.get('match_date'),
        today_tip: formData.get('edit_today_tip') === 'on',
        featured: formData.get('edit_featured') === 'on',
        maxodds_tip: formData.get('edit_maxodds_tip') === 'on',
        is_vip: formData.get('edit_is_vip') === 'on',
        is_vvip: formData.get('edit_is_vvip') === 'on',
        is_surepick: formData.get('edit_is_surepick') === 'on'
    };
    
    try {
        const response = await fetch(`/admin/fixtures/${fixtureId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert('Fixture updated successfully!');
            hideEditFixtureModal();
            location.reload();
        } else {
            alert('Failed to update fixture: ' + data.message);
        }
    } catch (error) {
        console.error('Error updating fixture:', error);
        alert('Error updating fixture. Please try again.');
    }
});

// Delete fixture
async function deleteFixture(fixtureId) {
    if (!confirm('Are you sure you want to delete this fixture?')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/fixtures/${fixtureId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert('Fixture deleted successfully!');
            location.reload();
        } else {
            alert('Failed to delete fixture: ' + data.message);
        }
    } catch (error) {
        console.error('Error deleting fixture:', error);
        alert('Error deleting fixture. Please try again.');
    }
}
</script>

@endsection
