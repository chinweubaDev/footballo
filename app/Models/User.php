<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'country',
        'is_premium',
        'premium_expires_at',
        'is_admin',
        'subscription_type',
        'vip_expires_at',
        'vvip_expires_at',
        'is_vip_active',
        'is_vvip_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_premium' => 'boolean',
            'is_admin' => 'boolean',
            'premium_expires_at' => 'datetime',
            'vip_expires_at' => 'datetime',
            'vvip_expires_at' => 'datetime',
            'is_vip_active' => 'boolean',
            'is_vvip_active' => 'boolean',
        ];
    }

    /**
     * Get the user's payments.
     */
    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    /**
     * Get the user's subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    /**
     * Check if user has active premium subscription.
     */
    public function hasActivePremium()
    {
        return $this->is_premium && $this->premium_expires_at && $this->premium_expires_at->isFuture();
    }

    /**
     * Check if user has active VIP subscription.
     */
    public function hasActiveVIP()
    {
        return $this->is_vip_active && $this->vip_expires_at && $this->vip_expires_at->isFuture();
    }

    /**
     * Check if user has active VVIP subscription.
     */
    public function hasActiveVVIP()
    {
        return $this->is_vvip_active && $this->vvip_expires_at && $this->vvip_expires_at->isFuture();
    }

    /**
     * Check if user has any active subscription (VIP or VVIP).
     */
    public function hasActiveSubscription()
    {
        return $this->hasActiveVIP() || $this->hasActiveVVIP();
    }

    /**
     * Get the highest active subscription level.
     */
    public function getActiveSubscriptionLevel()
    {
        if ($this->hasActiveVVIP()) {
            return 'vvip';
        } elseif ($this->hasActiveVIP()) {
            return 'vip';
        }
        return 'free';
    }

    /**
     * Upgrade user to VIP.
     */
    public function upgradeToVIP($durationDays = 30)
    {
        $expiresAt = now()->addDays((int) $durationDays);
        
        $this->subscription_type = 'vip';
        $this->is_vip_active = true;
        $this->is_vvip_active = false; // Deactivate VVIP if upgrading to VIP
        $this->vip_expires_at = $expiresAt;
        $this->vvip_expires_at = null;
        $this->is_premium = true;
        $this->premium_expires_at = $expiresAt;
        $this->save();
    }

    /**
     * Upgrade user to VVIP.
     */
    public function upgradeToVVIP($durationDays = 30)
    {
        $expiresAt = now()->addDays((int) $durationDays);
        
        $this->subscription_type = 'vvip';
        $this->is_vvip_active = true;
        $this->is_vip_active = false; // Deactivate VIP if upgrading to VVIP
        $this->vvip_expires_at = $expiresAt;
        $this->vip_expires_at = null;
        $this->is_premium = true;
        $this->premium_expires_at = $expiresAt;
        $this->save();
    }

    /**
     * Downgrade user to free.
     */
    public function downgradeToFree()
    {
        $this->subscription_type = 'free';
        $this->is_vip_active = false;
        $this->is_vvip_active = false;
        $this->vip_expires_at = null;
        $this->vvip_expires_at = null;
        $this->is_premium = false;
        $this->premium_expires_at = null;
        $this->save();
    }
}
