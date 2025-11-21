@extends('layouts.app')

@section('title', 'Home - Football Predictions')
<!-- Bootstrap CSS -->
<link 
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
  rel="stylesheet" 
  integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
  crossorigin="anonymous">
<style>
    td{
    font-size: 14px;
    }
    </style>
@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=" 60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" %3E%3Cg fill="none" fill-rule="evenodd" %3E%3Cg fill="%23ffffff" fill-opacity="0.05" %3E%3Ccircle cx="30" cy="30" r="2" /%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <div class="text-center">
            <div class="inline-flex items-center px-4 py-2 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-400 text-sm font-medium mb-8" data-aos="fade-up">
                <i class="fas fa-trophy mr-2"></i>
                Trusted by 10,000+ Successful Bettors
            </div>

            <h1 class="text-5xl lg:text-7xl font-bold text-white mb-6" data-aos="fade-up" data-aos-delay="100">
                Expert Football
                <span class="bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">Predictions</span>
            </h1>

            <p class="text-xl lg:text-2xl text-slate-300 mb-12 max-w-3xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="200">
                Get accurate predictions, expert analysis, and winning tips from our professional analysts.
                Join thousands of successful bettors who trust our insights.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center" data-aos="fade-up" data-aos-delay="300">
                <a href="{{ route('predictions') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <i class="fas fa-chart-line mr-3"></i>
                    View Predictions
                </a>
                <a href="{{ route('pricing') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white border-2 border-white/20 rounded-xl hover:bg-white/10 transition-all duration-200 backdrop-blur-sm">
                    <i class="fas fa-crown mr-3"></i>
                    Get VIP Access
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 max-w-4xl mx-auto" data-aos="fade-up" data-aos-delay="400">
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">85%</div>
                    <div class="text-slate-400">Win Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">10K+</div>
                    <div class="text-slate-400">Happy Users</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-white mb-2">24/7</div>
                    <div class="text-slate-400">Support</div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-24 bg-gradient-to-br from-yellow-50 to-yellow-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sure Picks Tips</h5>
                    </div>
                    <div class="">
                        @forelse($surePicksTips as $tip)
                        <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition duration-300 ease-in-out border border-gray-200 mb-3">
                            <!-- Match Header -->
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        @if($tip->home_team_logo)
                                        <img src="{{ $tip->home_team_logo }}" alt="{{ $tip->home_team }}" class="w-8 h-8 mr-2">
                                        @endif
                                        <h4 class="font-bold text-gray-900 text-lg">{{ $tip->home_team }}</h4>
                                    </div>
                                    <span class="text-gray-500 text-sm">vs</span>
                                    <div class="flex items-center">
                                        <h4 class="font-bold text-gray-900 text-lg">{{ $tip->away_team }}</h4>
                                        @if($tip->away_team_logo)
                                        <img src="{{ $tip->away_team_logo }}" alt="{{ $tip->away_team }}" class="w-8 h-8 ml-2">
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <span>{{ $tip->match_date->format('M d, Y') }}</span>
                                    <span class="font-medium">{{ $tip->match_time ? $tip->match_time->format('H:i') : '-' }}</span>
                                </div>
                            </div>

                            <!-- Prediction Details -->
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Sure Pick
                                    </span>
                                    @if($tip->odds)
                                    <span class="text-lg font-bold text-primary-600">{{ number_format($tip->odds, 2) }}</span>
                                    @endif
                                </div>

                                <!-- Prediction Tip -->
                                <div class="mb-3">
                                    <div class="text-sm text-gray-600 mb-1">Prediction:</div>
                                    <div class="font-bold text-gray-900 text-lg">{{ $tip->prediction }}</div>
                                </div>

                                <!-- League Info -->
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        @if($tip->league_logo)
                                        <img src="{{ $tip->league_logo }}" alt="{{ $tip->league_name }}" class="w-4 h-4 mr-1">
                                        @else
                                        <i class="fas fa-trophy mr-1"></i>
                                        @endif
                                        {{ $tip->league_name }}
                                    </span>
                                    @if($tip->is_featured)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-star mr-1"></i>Featured
                                    </span>
                                    @endif
                                </div>

                                <!-- Content Preview -->
                                @if($tip->content)
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($tip->content, 100) }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                            <i class="fas fa-info-circle text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-600">No sure picks available at the moment.</p>
                            <p class="text-sm text-gray-500 mt-1">Check back soon for new tips!</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-8">
            <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Free Tips of the Day</h5>
                   
                        @if($todayTipsByLeague->count() > 0)


        @foreach($todayTipsByLeague as $leagueName => $fixtures)
      
        <div class="mb-10" data-aos="fade-up">
        <h3 class="text-2xl font-semibold text-slate-800 mb-4 flex items-center" style="font-size: 13px;">
             
             <span><span class="font-weight-bold"> 
               {{$fixtures[0]['league_country']}}  - {{ $leagueName }}</span></span>
         </h3>

            <div class="overflow-x-auto bg-white rounded-2xl shadow-lg border border-slate-200" style="margin-bottom: 20px;">
            <table  width="100%" class="table  table-striped myTableSmall" >
                    <thead >



                        <tr style="text-align: center;">

                            <td style="width: 8%;">Time</td>

                            <td style="swidth: 30%;" class="text-ledft">League</td>
                            <td style="width: 47%;" class="text-ledft">Match</td>
                            <td style="dwidth: 10%;">Tip</td>
                            <td style="swidth: 10%;">Odds</td>

                        </tr>
                    </thead>
                    <tbody style="text-align: center!important;">
                        @foreach($fixtures as $fixture)
                  
                        @foreach($fixture->predictions as $prediction)
                       
                
                        <tr onclick="window.location.href='https://legitpredict.com/24791-free/elche-vs-real-sociedad'" style="height: 21px;">

                            <td style="background-color: #ffffff;">

                                <span> {{ $fixture->match_date->format(' H:i') }}</span>
                            </td>

                            <td class="text-ledft" style="display: flex;  justify-content:center"><img src="{{ $fixture->league_logo}}" width="25px" height="25px"   style="margin-right:10px;"  /> {{ $fixture->league_name }}</td>
                            <td  class="text-center"  >
                                <div style="display: flex;justify-content:center">
                                <span style="font-size:12px "> <img src="{{ $fixture->home_team_logo}}" width="25px" height="25px" /> {{ $fixture->home_team }}</span>
                                    <span style="color: #ff0000;margin-left:10px"><strong >VS</strong></span>
                                    <span style="display: flex;font-size:12px "><img src="{{ $fixture->away_team_logo}}" width="25px" height="25px"   style="margin-left:5px;margin-right:5px;width:25px; height:25px " />    {{ $fixture->away_team }}</span>
                            
                                </div>
                               
                                </td>
                            <td><span><strong>

                            {{ $prediction->tip }}

                                    </strong></span></td>
                            <td>

                            {{ $prediction->odds ?? '-' }}
                            </td>



                        </tr>
                   
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
               
            </div>
        </div>
        
        @endforeach
    </div>
</section>
@endif
                </div>
            </div>
            </div>
        </div>
    </div>
</section>
<!-- Tips of the Day Table -->

<div class="container">
<section class="py-5 mt-5 flex flex-col font-nunito-sans text-white items-center justify-center text-center gap-y-2.5 rounded-[27px] bg-[#000000D1] bg-[url('../assets/celebrate.jpg')] bg-center bg-cover bg-no-repeat bg-blend-overlay">
        <div class="p-2.5 max-w-[1066px]">
          <h2 class="font-bold text-2xl lg:text-4xl lg:leading-12">
            Sportybroker PREMIUM TIPS
          </h2>
          <p class="text-sm lg:text-lg">
            Make maximum
            <span class="text-[#00BF63] font-bold">PROFITS</span> from our sure
            “<span class="text-[#FF3131] font-bold">2 to 5 and 5 to 15</span>”
            daily Football Predictions.
          </p>
          <p>
            Enjoy up to
            <span class="text-[#00BF63] font-bold">95% Winning Rate</span> with
            our premium plan…
          </p>
        </div>
        

        <a href="https://www.sportybroker.com/premium" class="py-3.5 px-16 rounded-lg max-w-[250px] font-bai-jamjuree font-bold text-sm leading-5 text-[#F3F3F3]
       bg-[#5CC664] hover:bg-[#479B4D]
       flex gap-x-4 items-center">
          Access Now
          <svg width="18" height="15" viewBox="0 0 18 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 7.26855H3.5M17 7.26855L11 1.26855M17 7.26855L11 13.2686M17 7.26855H6.5" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </a>
      </section>
</div>
<div class="container">
    <div class="row mt-5">
<div class="col-lg-6">
     <div class="bg-[#FFFFFFF2] rounded-[20px] p-5 w-full">
        <h3 class="font-extrabold font-nunito-sans text-lg md:text-[21px] text-[#303030] text-center mb-4">
        RECENT VIP RESULTS
        </h3>

        <!-- Scrollable Table Container -->
        <div class="overflow-x-auto">
          <ul class="flex gap-4">
            @forelse($vipResults as $result)
                <li class="flex flex-col items-center min-w-[50px]">
                    <span class="py-2" style="font-size:11px;">{{ $result->date->format('d') }}</span>
                    <span class="py-2" style="font-size:11px;">{{ $result->date->format('M') }}</span>
                    <span class="py-2" style="font-size:11px;">{{ $result->odds }}<br>Odds</span>
                    <span class="py-2">
                        <div class="h-6 w-6 rounded-full {{ $result->status === 'win' ? 'bg-[#4E9D00]' : 'bg-red-500' }} flex items-center justify-center">
                            @if($result->status === 'win')
                                <img src="https://www.sportybroker.com/assets/tick.svg" alt="tick">
                            @else
                                <i class="fas fa-times text-white text-xs"></i>
                            @endif
                        </div>
                    </span>
                </li>
            @empty
                <li class="flex items-center justify-center w-full py-4">
                    <span class="text-sm text-gray-500">No results yet</span>
                </li>
            @endforelse
          </ul>
        </div>
      </div>
        </div>
        <div class="col-lg-6">
        <div class="bg-[#FFFFFFF2] rounded-[20px] p-5 w-full">
                <h3 class="font-extrabold font-nunito-sans text-lg md:text-[21px] text-[#303030] text-center mb-4">
                RECENT VVIP RESULTS
                </h3>

                <!-- Scrollable Table Container -->
                <div class="overflow-x-auto">
                <ul class="flex gap-4">
                    @forelse($vvipResults as $result)
                        <li class="flex flex-col items-center min-w-[50px]">
                            <span class="py-2" style="font-size:11px;">{{ $result->date->format('d') }}</span>
                            <span class="py-2" style="font-size:11px;">{{ $result->date->format('M') }}</span>
                            <span class="py-2" style="font-size:11px;">{{ $result->odds }}<br>Odds</span>
                            <span class="py-2">
                                <div class="h-6 w-6 rounded-full {{ $result->status === 'win' ? 'bg-[#4E9D00]' : 'bg-red-500' }} flex items-center justify-center">
                                    @if($result->status === 'win')
                                        <img src="https://www.sportybroker.com/assets/tick.svg" alt="tick">
                                    @else
                                        <i class="fas fa-times text-white text-xs"></i>
                                    @endif
                                </div>
                            </span>
                        </li>
                    @empty
                        <li class="flex items-center justify-center w-full py-4">
                            <span class="text-sm text-gray-500">No results yet</span>
                        </li>
                    @endforelse
                </ul>
                </div>
        </div> 
        </div>
    </div>
</div>

<!-- Most Featured Predictions Table -->
@if($featuredByLeague->count() > 0)
<section class="py-24 bg-gradient-to-br from-blue-50 to-blue-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
            <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-4xl font-bold text-slate-900 mb-4">
                ⭐ Most Featured Predictions
            </h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                The most anticipated upcoming matches with expert betting tips.
            </p>
        </div>
<br><br>
        @foreach($featuredByLeague as $leagueName => $fixtures)
        <div class="mb-10" data-aos="fade-up">
        <h3 class="text-2xl font-semibold text-slate-800 mb-1 flex items-center" style="font-size: 13px;">
             
             <span><span class="font-weight-bold"> 
               {{$fixtures[0]['league_country']}}  - {{ $leagueName }}</span></span>
         </h3>


            <div class="overflow-x-auto bg-white rounded-2xl shadow-lg border border-slate-200" style="margin-bottom: 20px;">
            <table  width="100%" class="table  table-striped myTableSmall" >
                    <thead >



                        <tr style="text-align: center;">

                            <td style="width: 8%;">Time</td>

                            <td style="swidth: 30%;" class="text-ledft">League</td>
                            <td style="swidth: 47%;" class="text-ledft">Home Team</td>
                            <td>Score</td>
                            <td>Away Team</td>
                            <td style="dwidth: 10%;">Tip</td>
                            <td style="swidth: 10%;">Odds</td>

                        </tr>
                    </thead>
                    <tbody style="text-align: center!important;">
                        @foreach($fixtures as $fixture)
                  
                        @foreach($fixture->predictions as $prediction)
                       
                
                        <tr onclick="window.location.href='https://legitpredict.com/24791-free/elche-vs-real-sociedad'" style="height: 21px;">

                            <td style="background-color: #ffffff;">

                                <span> {{ $fixture->match_date->format(' H:i') }}</span>
                            </td>

                            <td class="text-ledft" style="display: flex;  justify-content:center"><img src="{{ $fixture->league_logo}}" width="20px"  style="margin-right:10px;"  /> {{ $fixture->league_name }}</td>
                            <td>   <span style="font-size:12px;display:flex;justify-content:center;gap : 5px "> <img src="{{ $fixture->home_team_logo}}" width="20px" /> {{ $fixture->home_team }}</span></td>
                            <td> <span style="border: 1px solid #eee;padding:2px">1- 9</span></td>
                            <td  class="text-center"  >
                               
                                    <span style="display: flex;justify-content:center;gap : 5px;font-size:12px "><img src="{{ $fixture->away_team_logo}}" width="20px" style="margin-left:5px;margin-right:5px;" />    {{ $fixture->away_team }}</span>
                            
                       
                               
                                </td>
                            <td><span><strong>

                            {{ $prediction->tip }}

                                    </strong></span></td>
                            <td>

                            {{ $prediction->odds ?? '-' }}
                            </td>



                        </tr>
                   
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
               
            </div>
        </div>
        @endforeach
            </div>
        </div>
     
    </div>
</section>
@endif





<!-- CTA Section -->
<section class="py-24 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-white mb-6" data-aos="fade-up">
            Ready to Start Winning?
        </h2>
        <p class="text-xl text-slate-300 mb-8" data-aos="fade-up" data-aos-delay="100">
            Join thousands of successful bettors who trust our expert predictions.
            Start your winning journey today!
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center" data-aos="fade-up" data-aos-delay="200">
            <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl hover:from-primary-600 hover:to-primary-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-rocket mr-3"></i>
                Get Started Now
            </a>
            <a href="{{ route('predictions') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white border-2 border-white/20 rounded-xl hover:bg-white/10 transition-all duration-200 backdrop-blur-sm">
                <i class="fas fa-eye mr-3"></i>
                View Sample Predictions
            </a>
        </div>
    </div>
</section>
@endsection