@extends('layouts.app')

@section('title', 'Terms of Service')
@section('meta_description', 'Read our Terms of Service to understand the rules and regulations for using our football predictions website and services.')

@section('content')
<div class="bg-white min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-slate-900 mb-4">Terms of Service</h1>
            <p class="text-slate-500">Last updated: {{ date('F d, Y') }}</p>
        </div>

        <div class="prose prose-slate max-w-none text-slate-600 space-y-6" data-aos="fade-up" data-aos-delay="100">
            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">1. Acceptance of Terms</h3>
                <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">2. Use License</h3>
                <p>Permission is granted to temporarily download one copy of the materials (information or software) on Football Predictions' website for personal, non-commercial transitory viewing only.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">3. Disclaimer</h3>
                <p>The materials on Football Predictions' website are provided on an 'as is' basis. Football Predictions makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
            </section>

            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">4. Limitations</h3>
                <p>In no event shall Football Predictions or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on Football Predictions' website.</p>
            </section>
            
            <section>
                <h3 class="text-xl font-bold text-slate-900 mb-3">5. Betting Disclaimer</h3>
                <p>Our predictions are for informational purposes only. We do not encourage gambling and are not responsible for any financial losses. Please gamble responsibly and only bet what you can afford to lose.</p>
            </section>
        </div>
    </div>
</div>
@endsection
