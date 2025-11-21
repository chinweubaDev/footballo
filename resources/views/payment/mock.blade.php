@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-credit-card text-green-600 text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Mock Payment Gateway</h2>
                <p class="text-gray-600 mb-6">This is a development mock payment page</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Transaction ID:</span>
                        <span class="font-medium">{{ $payment->transaction_id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Amount:</span>
                        <span class="font-medium">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Plan:</span>
                        <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $payment->plan_type)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Duration:</span>
                        <span class="font-medium">{{ $payment->plan_duration_days }} days</span>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <button onclick="simulatePayment('success')" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-check mr-2"></i>
                    Simulate Successful Payment
                </button>
                
                <button onclick="simulatePayment('failed')" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-times mr-2"></i>
                    Simulate Failed Payment
                </button>
                
                <a href="{{ route('pricing') }}" 
                   class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Pricing
                </a>
            </div>

            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    This is a development mock payment gateway. In production, this would redirect to the actual payment provider.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function simulatePayment(status) {
    const loadingModal = document.createElement('div');
    loadingModal.id = 'loadingModal';
    loadingModal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    loadingModal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="spinner mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-gray-900">Processing Payment</h3>
                <p class="text-sm text-gray-600 mt-2">Simulating ${status} payment...</p>
            </div>
        </div>
    `;
    document.body.appendChild(loadingModal);

    // Simulate processing delay
    setTimeout(() => {
        document.body.removeChild(loadingModal);
        
        if (status === 'success') {
            // Redirect to success page or dashboard
            window.location.href = '/dashboard?payment=success';
        } else {
            // Show error message
            alert('Payment simulation failed. Please try again.');
        }
    }, 2000);
}
</script>

<style>
.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 2s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endsection
