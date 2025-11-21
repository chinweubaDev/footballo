<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'flutterwave_reference',
        'paypal_order_id',
        'skrill_transaction_id',
        'crypto_address',
        'crypto_amount',
        'crypto_type',
        'amount',
        'currency',
        'payment_method',
        'status',
        'plan_type',
        'plan_duration_days',
        'expires_at',
        'payment_details',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'payment_details' => 'array',
        ];
    }

    /**
     * Get the user that owns the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription for this payment.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Scope for completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
