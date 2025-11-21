<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PricingController extends Controller
{
    public function index(Request $request)
    {
        // Get selected country from request or default to user's country
        $selectedCountry = $request->get('country', $this->getUserCountry());
        
        // Get pricing plans from database
        $pricingPlans = PricingPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        // Get all available countries for dropdown
        $availableCountries = $this->getAvailableCountries();
        
        return view('pricing.index', compact('pricingPlans', 'selectedCountry', 'availableCountries'));
    }

    public function getPricingByCountryAjax(Request $request)
    {
        $country = $request->get('country', 'Nigeria');
        
        Log::info('Pricing request for country:', ['country' => $country]);
        
        // Get pricing plans from database
        $pricingPlans = PricingPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        $pricing = [];
        foreach ($pricingPlans as $plan) {
            $price = $plan->getPriceForCountry($country);
            $currency = $plan->getCurrencyForCountry($country);
            
            Log::info('Plan pricing:', [
                'plan_key' => $plan->key,
                'country' => $country,
                'price' => $price,
                'currency' => $currency
            ]);
            
            $pricing[$plan->key] = [
                'price' => $price,
                'currency' => $currency
            ];
        }
        
        Log::info('Final pricing data:', ['pricing' => $pricing]);
        
        return response()->json([
            'status' => 'success',
            'pricing' => $pricing,
            'country' => $country
        ]);
    }

    private function getUserCountry()
    {
        // Try to get country from user's profile if logged in
        if (Auth::check() && Auth::user()->country) {
            return Auth::user()->country;
        }

        // Try to get country from IP geolocation
        $ip = request()->ip();
        // You can integrate with a geolocation service here
        // For now, default to Nigeria
        return 'Nigeria';
    }

    private function getAvailableCountries()
    {
        return [
            'Nigeria' => 'Nigeria (NGN)',
            'Kenya' => 'Kenya (KES)',
            'Ghana' => 'Ghana (GHS)',
            'Zimbabwe' => 'Zimbabwe (USD)',
            'Zambia' => 'Zambia (ZMW)',
            'Uganda' => 'Uganda (UGX)',
            'Tanzania' => 'Tanzania (TZS)',
            'Other' => 'Other Countries (USD)'
        ];
    }
}
