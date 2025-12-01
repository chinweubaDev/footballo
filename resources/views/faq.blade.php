@extends('layouts.app')

@section('title', 'Frequently Asked Questions (FAQ)')
@section('meta_description', 'Find answers to frequently asked questions about our football predictions, VIP subscriptions, payment methods, and more.')
@section('meta_keywords', 'faq, questions, help, subscriptions, payments, predictions accuracy')

@section('content')
<div class="bg-slate-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-slate-900">Frequently Asked Questions</h1>
            <p class="mt-4 text-lg text-slate-600">Everything you need to know about our services.</p>
        </div>

        <div class="space-y-6">
            <!-- FAQ Item 1 -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow duration-200" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-lg font-semibold text-slate-900 mb-2 flex items-center">
                    <i class="fas fa-chart-line text-primary-500 mr-3"></i>
                    How accurate are the predictions?
                </h3>
                <p class="text-slate-600 ml-8">Our predictions are based on advanced statistical models and expert analysis. While we strive for high accuracy, sports outcomes are unpredictable, and we cannot guarantee 100% success.</p>
            </div>
             <!-- FAQ Item 2 -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow duration-200" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-lg font-semibold text-slate-900 mb-2 flex items-center">
                    <i class="fas fa-crown text-primary-500 mr-3"></i>
                    What is the difference between Free and VIP?
                </h3>
                <p class="text-slate-600 ml-8">Free users get access to basic daily predictions. VIP members receive higher confidence tips, more detailed analysis, and access to premium categories.</p>
            </div>
             <!-- FAQ Item 3 -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow duration-200" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-lg font-semibold text-slate-900 mb-2 flex items-center">
                    <i class="fas fa-ban text-primary-500 mr-3"></i>
                    How do I cancel my subscription?
                </h3>
                <p class="text-slate-600 ml-8">You can cancel your subscription at any time from your account settings page. The cancellation will take effect at the end of your current billing period.</p>
            </div>
            <!-- FAQ Item 4 -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow duration-200" data-aos="fade-up" data-aos-delay="400">
                <h3 class="text-lg font-semibold text-slate-900 mb-2 flex items-center">
                    <i class="fas fa-wallet text-primary-500 mr-3"></i>
                    What payment methods do you accept?
                </h3>
                <p class="text-slate-600 ml-8">We accept major credit cards, debit cards, and various local payment methods depending on your region. All transactions are secure and encrypted.</p>
            </div>
        </div>
    </div>
</div>
@endsection
