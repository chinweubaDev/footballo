<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $fillable = [
        'name',
        'key',
        'price_usd',
        'price_ngn',
        'price_kes',
        'price_ghs',
        'price_zwl',
        'price_zmw',
        'price_ugx',
        'price_tzs',
        'duration_days',
        'features',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_usd' => 'decimal:2',
        'price_ngn' => 'decimal:2',
        'price_kes' => 'decimal:2',
        'price_ghs' => 'decimal:2',
        'price_zwl' => 'decimal:2',
        'price_zmw' => 'decimal:2',
        'price_ugx' => 'decimal:2',
        'price_tzs' => 'decimal:2',
    ];

    public function getPriceForCountry($country)
    {
        $country = strtolower($country);
        
        if (in_array($country, ['nigeria', 'kenya', 'ghana', 'zimbabwe', 'zambia', 'uganda', 'tanzania'])) {
            $currencyField = 'price_' . $this->getCurrencyCode($country);
            return $this->$currencyField ?? $this->price_usd;
        }
        
        return $this->price_usd;
    }

    public function getCurrencyForCountry($country)
    {
        $country = strtolower($country);
        
        $currencyMap = [
            'nigeria' => 'NGN',
            'kenya' => 'KES',
            'ghana' => 'GHS',
            'zimbabwe' => 'ZWL',
            'zambia' => 'ZMW',
            'uganda' => 'UGX',
            'tanzania' => 'TZS',
        ];

        return $currencyMap[$country] ?? 'USD';
    }

    private function getCurrencyCode($country)
    {
        $country = strtolower($country);
        
        $currencyMap = [
            'nigeria' => 'ngn',
            'kenya' => 'kes',
            'ghana' => 'ghs',
            'zimbabwe' => 'zwl',
            'zambia' => 'zmw',
            'uganda' => 'ugx',
            'tanzania' => 'tzs',
        ];

        return $currencyMap[$country] ?? 'usd';
    }
}
