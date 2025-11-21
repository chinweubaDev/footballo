@extends('layouts.app')

@section('title', 'VVIP Tips - Ultimate Football Predictions')

@section('content')
<!-- VVIP Header -->
<section class="relative overflow-hidden bg-gradient-to-br from-purple-600 via-purple-700 to-purple-800">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-purple-500/20 border border-purple-400/30 text-purple-100 text-sm font-medium mb-8" data-aos="fade-up">
                <i class="fas fa-gem mr-2"></i>
                VVIP Exclusive Access
            </div>
            
            <h1 class="text-5xl lg:text-6xl font-bold text-white mb-6" data-aos="fade-up" data-aos-delay="100">
                VVIP Tips
            </h1>
            
            <p class="text-xl lg:text-2xl text-purple-100 mb-8 max-w-3xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="200">
                Ultimate premium football predictions for VVIP members. Get exclusive insights and personal consultation.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center" data-aos="fade-up" data-aos-delay="300">
                <div class="inline-flex items-center px-6 py-3 bg-white/10 border border-white/20 text-white rounded-xl backdrop-blur-sm">
                    <i class="fas fa-gem mr-3"></i>
                    <span>{{ $tips->total() }} Exclusive Tips</span>
                </div>
                <div class="inline-flex items-center px-6 py-3 bg-white/10 border border-white/20 text-white rounded-xl backdrop-blur-sm">
                    <i class="fas fa-trophy mr-3"></i>
                    <span>90% Win Rate</span>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Access Control Check -->
    @if(!auth()->check())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6 mb-8" data-aos="fade-up">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-lock text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-red-800">Authentication Required</h3>
                    <p class="text-red-600">You must be logged in to access VVIP tips.</p>
                </div>
            </div>
        </div>
    @elseif(!auth()->user()->hasActiveVVIP())
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 mb-8" data-aos="fade-up">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-gem text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-yellow-800">VVIP Subscription Required</h3>
                        <p class="text-yellow-600">You need an active VVIP subscription to access these tips.</p>
                    </div>
                </div>
                <a href="{{ route('pricing') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors duration-200 font-semibold">
                    <i class="fas fa-gem mr-2"></i>
                    Upgrade to VVIP
                </a>
            </div>
        </div>
    @endif

    @if(auth()->check() && auth()->user()->hasActiveVVIP())
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- VVIP Tips Content -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up">
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-slate-900 flex items-center">
                                <i class="fas fa-gem mr-3 text-purple-600"></i>
                                VVIP Tips
                            </h2>
                            <span class="bg-purple-100 text-purple-800 text-sm font-medium px-3 py-1 rounded-full">
                                {{ $tips->total() }} Exclusive Tips
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        @if($tips->count() > 0)
                            <!-- VVIP Tips from Database -->
                            <div class="space-y-6">
                                @foreach($tips as $tip)
                                <div class="bg-gradient-to-r from-purple-50 to-white border border-purple-200 rounded-2xl p-6 hover:shadow-lg transition-all duration-200 {{ $tip->is_featured ? 'ring-2 ring-purple-500' : '' }}" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                                    @if($tip->is_featured)
                                    <div class="flex items-center mb-4">
                                        <span class="bg-yellow-100 text-yellow-800 text-sm font-medium px-3 py-1 rounded-full">
                                            <i class="fas fa-star mr-1"></i>Featured
                                        </span>
                                    </div>
                                    @endif
                                    
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <span class="bg-purple-100 text-purple-800 text-sm font-medium px-3 py-1 rounded-full mr-3">
                                                VVIP
                                            </span>
                                            @if($tip->league_name)
                                                <span class="text-sm text-slate-600">{{ $tip->league_name }}</span>
                                            @endif
                                        </div>
                                        @if($tip->prediction)
                                            <span class="text-sm font-semibold text-slate-900 bg-slate-100 px-3 py-1 rounded-full">{{ $tip->prediction }}</span>
                                        @endif
                                    </div>
                                    
                                    <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $tip->title }}</h3>
                                    
                                    @if($tip->home_team && $tip->away_team)
                                        <div class="bg-slate-50 rounded-xl p-4 mb-4">
                                            <h4 class="text-lg font-semibold text-slate-800 text-center">{{ $tip->home_team }} vs {{ $tip->away_team }}</h4>
                                        </div>
                                    @endif
                                    
                                    <p class="text-slate-600 mb-4 leading-relaxed">{{ $tip->content }}</p>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            @if($tip->match_date && $tip->match_time)
                                                <span class="text-sm text-slate-500 flex items-center">
                                                    <i class="fas fa-calendar mr-2"></i>
                                                    {{ $tip->match_date->format('M d, Y') }} at {{ $tip->match_time }}
                                                </span>
                                            @else
                                                <span class="text-sm text-slate-500 flex items-center">
                                                    <i class="fas fa-clock mr-2"></i>
                                                    {{ $tip->created_at->format('M d, Y H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="flex items-center space-x-3">
                                            @if($tip->odds)
                                                <span class="text-sm font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">
                                                    Odds: {{ $tip->odds }}
                                                </span>
                                            @endif
                                            
                                            <span class="text-sm font-medium px-3 py-1 rounded-full 
                                                @if($tip->status === 'won') bg-green-100 text-green-800
                                                @elseif($tip->status === 'lost') bg-red-100 text-red-800
                                                @elseif($tip->status === 'void') bg-yellow-100 text-yellow-800
                                                @else bg-purple-100 text-purple-800
                                                @endif">
                                                {{ ucfirst($tip->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <!-- Pagination -->
                            <div class="mt-8">
                                {{ $tips->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="w-20 h-20 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-gem text-purple-600 text-3xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-900 mb-4">No VVIP Tips Available</h3>
                                <p class="text-slate-600 mb-6">Check back later for new VVIP predictions.</p>
                                <a href="{{ route('predictions') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors duration-200 font-semibold">
                                    <i class="fas fa-chart-line mr-2"></i>
                                    View All Predictions
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- VVIP Features Sidebar -->
            <div class="space-y-6">
                <!-- Subscription Status -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
                        <h3 class="text-lg font-semibold text-slate-900 flex items-center">
                            <i class="fas fa-gem mr-2 text-purple-600"></i>
                            Your VVIP Status
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Plan</span>
                                <span class="text-sm font-semibold text-purple-600">VVIP</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Expires</span>
                                <span class="text-sm font-semibold text-slate-900">{{ auth()->user()->vvip_expires_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-slate-600">Days Remaining</span>
                                <span class="text-sm font-semibold text-green-600">{{ auth()->user()->vvip_expires_at->diffInDays(now()) }} days</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="w-full bg-slate-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ auth()->user()->vvip_expires_at->diffInDays(now()) > 0 ? min(100, (auth()->user()->vvip_expires_at->diffInDays(now()) / 30) * 100) : 0 }}%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-2 text-center">
                                {{ auth()->user()->vvip_expires_at->diffInDays(now()) }} days remaining
                            </p>
                        </div>
                    </div>
                </div>

                <!-- VVIP Features -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="300">
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
                        <h3 class="text-lg font-semibold text-slate-900 flex items-center">
                            <i class="fas fa-star mr-2 text-purple-600"></i>
                            VVIP Features
                        </h3>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Exclusive VVIP Tips</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Personal Consultant</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Expert Analysis</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">24/7 Premium Support</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Live Updates</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-3"></i>
                                <span class="text-sm text-slate-600">Exclusive Insights</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Personal Consultant -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white" data-aos="fade-up" data-aos-delay="400">
                    <h3 class="text-xl font-bold mb-2">Personal Consultant</h3>
                    <p class="text-purple-100 text-sm mb-4">Get one-on-one consultation with our expert analysts for personalized betting strategies.</p>
                    <a href="#" class="inline-flex items-center w-full justify-center px-6 py-3 bg-white text-purple-600 rounded-xl hover:bg-purple-50 transition-colors duration-200 font-semibold">
                        <i class="fas fa-user-tie mr-2"></i>
                        Contact Consultant
                    </a>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden" data-aos="fade-up" data-aos-delay="500">
                    <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900 flex items-center">
                            <i class="fas fa-bolt mr-2 text-primary-500"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('tips.vip') }}" class="flex items-center p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                                <i class="fas fa-crown text-blue-500 mr-3"></i>
                                <span class="text-sm font-medium text-slate-700">VIP Tips</span>
                            </a>
                            <a href="{{ route('predictions') }}" class="flex items-center p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                                <i class="fas fa-chart-line text-green-500 mr-3"></i>
                                <span class="text-sm font-medium text-slate-700">All Predictions</span>
                            </a>
                            <a href="{{ route('dashboard') }}" class="flex items-center p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                                <i class="fas fa-tachometer-alt text-purple-500 mr-3"></i>
                                <span class="text-sm font-medium text-slate-700">Dashboard</span>
                            </a>
                            <a href="{{ route('profile') }}" class="flex items-center p-3 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors duration-200">
                                <i class="fas fa-user text-orange-500 mr-3"></i>
                                <span class="text-sm font-medium text-slate-700">Profile</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection