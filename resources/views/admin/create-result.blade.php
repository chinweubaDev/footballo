@extends('layouts.app')

@section('title', 'Add Result - Admin Dashboard')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Add New Result</h1>
                    <p class="text-gray-600">Add a new VIP or VVIP result</p>
                </div>
                <a href="{{ route('admin.results') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-150 ease-in-out">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Results
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form action="{{ route('admin.results.store') }}" method="POST">
                @csrf

                <!-- Date -->
                <div class="mb-6">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="{{ old('date') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('date') border-red-500 @enderror"
                           required>
                    @error('date')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Odds -->
                <div class="mb-6">
                    <label for="odds" class="block text-sm font-medium text-gray-700 mb-2">
                        Odds <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="odds" 
                           name="odds" 
                           step="0.01"
                           min="1"
                           value="{{ old('odds') }}"
                           placeholder="e.g., 2.03"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('odds') border-red-500 @enderror"
                           required>
                    @error('odds')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror"
                            required>
                        <option value="">Select Status</option>
                        <option value="win" {{ old('status') === 'win' ? 'selected' : '' }}>Win</option>
                        <option value="lose" {{ old('status') === 'lose' ? 'selected' : '' }}>Lose</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Type <span class="text-red-500">*</span>
                    </label>
                    <select id="type" 
                            name="type" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('type') border-red-500 @enderror"
                            required>
                        <option value="">Select Type</option>
                        <option value="vip" {{ old('type') === 'vip' ? 'selected' : '' }}>VIP</option>
                        <option value="vvip" {{ old('type') === 'vvip' ? 'selected' : '' }}>VVIP</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('admin.results') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-150 ease-in-out">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                        <i class="fas fa-save mr-2"></i>Add Result
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
