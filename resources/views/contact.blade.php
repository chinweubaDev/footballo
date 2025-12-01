@extends('layouts.app')

@section('title', 'Contact Us - Get in Touch')
@section('meta_description', 'Have questions or feedback? Reach out to the Football Predictions team. We value your input and are ready to assist you.')
@section('meta_keywords', 'contact us, feedback, customer service, support, inquiries')

@section('content')
<div class="bg-white min-h-screen py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <h1 class="text-3xl font-bold text-slate-900">Contact Us</h1>
            <p class="mt-4 text-lg text-slate-600">Have a question or feedback? We'd love to hear from you.</p>
        </div>

        <div class="bg-slate-50 rounded-2xl p-8 shadow-sm border border-slate-100" data-aos="fade-up" data-aos-delay="100">
            <form class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-3 border" placeholder="Your Name">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-3 border" placeholder="you@example.com">
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-slate-700">Message</label>
                    <textarea id="message" name="message" rows="4" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm p-3 border" placeholder="How can we help?"></textarea>
                </div>
                <div>
                    <button type="button" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-sm font-medium text-white bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 transform hover:-translate-y-0.5">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
