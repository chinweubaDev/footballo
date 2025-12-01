@extends('layouts.app')

@section('title', 'Support Center - We Are Here To Help')
@section('meta_description', 'Need help? Contact our support team via email or live chat. Find answers to common questions in our FAQ section.')
@section('meta_keywords', 'customer support, help center, contact us, faq, football predictions support')

@section('content')
<div class="bg-slate-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16" data-aos="fade-up">
            <h1 class="text-4xl font-bold text-slate-900 mb-4">How can we help you?</h1>
            <p class="text-xl text-slate-600">We're here to support your journey.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1 -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center hover:-translate-y-1 transition-transform duration-300" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-envelope text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Email Support</h3>
                <p class="text-slate-600 mb-6">Get a response within 24 hours.</p>
                <a href="mailto:support@footballpredictions.com" class="text-blue-600 font-semibold hover:text-blue-700 transition-colors">support@footballpredictions.com &rarr;</a>
            </div>

            <!-- Card 2 -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center hover:-translate-y-1 transition-transform duration-300" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-comments text-2xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Live Chat</h3>
                <p class="text-slate-600 mb-6">Chat with our support team.</p>
                <button class="text-green-600 font-semibold hover:text-green-700 transition-colors">Start Chat &rarr;</button>
            </div>

            <!-- Card 3 -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center hover:-translate-y-1 transition-transform duration-300" data-aos="fade-up" data-aos-delay="300">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-question-circle text-2xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">FAQ</h3>
                <p class="text-slate-600 mb-6">Find answers instantly.</p>
                <a href="{{ route('faq') }}" class="text-purple-600 font-semibold hover:text-purple-700 transition-colors">Visit FAQ &rarr;</a>
            </div>
        </div>
    </div>
</div>
@endsection
