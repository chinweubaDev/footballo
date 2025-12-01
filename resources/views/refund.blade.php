@extends('layouts.app')

@section('title', 'Refund Policy')
@section('meta_description', 'Understand our refund policy for VIP and VVIP subscriptions. We offer a 7-day money-back guarantee for new subscribers.')

@section('content')
<div class="bg-white min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Refund Policy</h1>
            <p class="text-slate-500">Last updated: {{ date('F d, Y') }}</p>
        </div>

        <div class="prose prose-slate max-w-none text-slate-600 space-y-6" data-aos="fade-up" data-aos-delay="100">
            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">1. Subscription Refunds</h3>
                <p>We offer a 7-day money-back guarantee for all new VIP and VVIP subscriptions. If you are not satisfied with our service within the first 7 days, you can request a full refund.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">2. How to Request a Refund</h3>
                <p>To request a refund, please contact our support team at <a href="mailto:support@footballpredictions.com" class="text-primary-600 hover:text-primary-700">support@footballpredictions.com</a> with your order details and reason for the refund.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">3. Processing Time</h3>
                <p>Refunds are typically processed within 5-10 business days. The time it takes for the refund to appear in your account depends on your payment provider.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">4. Non-Refundable Items</h3>
                <p>Certain items or services may be non-refundable. This includes any one-time purchases or services that have already been fully delivered.</p>
            </section>
        </div>
    </div>
</div>
@endsection
