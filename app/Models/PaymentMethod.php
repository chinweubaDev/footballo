<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'type',
        'crypto_type',
        'display_name',
        'description',
        'icon',
        'color',
        'config',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean'
    ];

    public function getConfigValue($key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    public function setConfigValue($key, $value)
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->config = $config;
    }
}
