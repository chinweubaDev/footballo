<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_id',
        'category',
        'tip',
        'confidence',
        'odds',
        'analysis',
        'is_premium',
        'is_maxodds',
        'status',
        'today_tip_content',
        'featured_tip_content',
        'vip_tip_content',
        'vvip_tip_content',
        'surepick_tip_content',
        'maxodds_tip_content',
    ];

    protected function casts(): array
    {
        return [
            'odds' => 'decimal:2',
            'is_premium' => 'boolean',
            'is_maxodds' => 'boolean',
        ];
    }

    /**
     * Get the fixture that owns the prediction.
     */
    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }

    /**
     * Scope for premium predictions.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope for maxodds predictions.
     */
    public function scopeMaxodds($query)
    {
        return $query->where('is_maxodds', true);
    }

    /**
     * Scope for predictions by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
