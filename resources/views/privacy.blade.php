@extends('layouts.app')

@section('title', 'Privacy Policy')
@section('meta_description', 'Your privacy is important to us. Learn how we collect, use, and protect your personal information in our Privacy Policy.')

@section('content')
<div class="bg-white min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Privacy Policy</h1>
            <p class="text-slate-500">Last updated: {{ date('F d, Y') }}</p>
        </div>

        <div class="prose prose-slate max-w-none text-slate-600 space-y-6" data-aos="fade-up" data-aos-delay="100">
            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">1. Information We Collect</h3>
                <p>We collect information you provide directly to us, such as when you create an account, subscribe to our newsletter, or contact us for support.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">2. How We Use Your Information</h3>
                <p>We use the information we collect to provide, maintain, and improve our services, to process your transactions, and to communicate with you.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">3. Information Sharing</h3>
                <p>We do not share your personal information with third parties except as described in this privacy policy or with your consent.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">4. Security</h3>
                <p>We take reasonable measures to help protect information about you from loss, theft, misuse and unauthorized access, disclosure, alteration and destruction.</p>
            </section>
            
            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">5. Cookies</h3>
                <p>We use cookies to improve your experience on our website. By using our website, you agree to the use of cookies in accordance with this policy.</p>
            </section>
        </div>
    </div>
</div>
@endsection
