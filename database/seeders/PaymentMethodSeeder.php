<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethod::truncate();

        $methods = [
            [
                'name' => 'Flutterwave',
                'type' => 'flutterwave',
                'display_name' => 'Flutterwave',
                'description' => 'Cards, Bank Transfer, Mobile Money',
                'icon' => 'fas fa-credit-card',
                'color' => 'blue',
                'is_active' => true,
                'config' => [
                    'public_key' => '',
                    'secret_key' => '',
                    'encryption_key' => ''
                ],
                'sort_order' => 1
            ],
            [
                'name' => 'PayPal',
                'type' => 'paypal',
                'display_name' => 'PayPal',
                'description' => 'PayPal Account & Cards',
                'icon' => 'fab fa-paypal',
                'color' => 'blue',
                'is_active' => true,
                'config' => [
                    'client_id' => '',
                    'client_secret' => '',
                    'email' => ''
                ],
                'sort_order' => 2
            ],
            [
                'name' => 'Skrill',
                'type' => 'skrill',
                'display_name' => 'Skrill',
                'description' => 'Digital Wallet',
                'icon' => 'fas fa-wallet',
                'color' => 'orange',
                'is_active' => true,
                'config' => [
                    'email' => '',
                    'api_key' => ''
                ],
                'sort_order' => 3
            ],
            [
                'name' => 'Bitcoin',
                'type' => 'crypto',
                'crypto_type' => 'BTC',
                'display_name' => 'Bitcoin (BTC)',
                'description' => 'Cryptocurrency',
                'icon' => 'fab fa-bitcoin',
                'color' => 'orange',
                'is_active' => true,
                'config' => [
                    'address' => '',
                    'network' => 'bitcoin'
                ],
                'sort_order' => 4
            ],
            [
                'name' => 'Tether',
                'type' => 'crypto',
                'crypto_type' => 'USDT',
                'display_name' => 'Tether (USDT)',
                'description' => 'Stablecoin',
                'icon' => 'fas fa-coins',
                'color' => 'green',
                'is_active' => true,
                'config' => [
                    'address' => '',
                    'network' => 'tron'
                ],
                'sort_order' => 5
            ],
            [
                'name' => 'Ethereum',
                'type' => 'crypto',
                'crypto_type' => 'ETH',
                'display_name' => 'Ethereum (ETH)',
                'description' => 'Cryptocurrency',
                'icon' => 'fab fa-ethereum',
                'color' => 'blue',
                'is_active' => true,
                'config' => [
                    'address' => '',
                    'network' => 'ethereum'
                ],
                'sort_order' => 6
            ],
            [
                'name' => 'Binance Coin',
                'type' => 'crypto',
                'crypto_type' => 'BNB',
                'display_name' => 'Binance Coin (BNB)',
                'description' => 'Cryptocurrency',
                'icon' => 'fas fa-coins',
                'color' => 'yellow',
                'is_active' => true,
                'config' => [
                    'address' => '',
                    'network' => 'bsc'
                ],
                'sort_order' => 7
            ],
            [
                'name' => 'TRON',
                'type' => 'crypto',
                'crypto_type' => 'TRX',
                'display_name' => 'TRON (TRX)',
                'description' => 'Cryptocurrency',
                'icon' => 'fas fa-coins',
                'color' => 'red',
                'is_active' => true,
                'config' => [
                    'address' => '',
                    'network' => 'tron'
                ],
                'sort_order' => 8
            ],
            [
                'name' => 'Bank Transfer',
                'type' => 'bank_transfer',
                'display_name' => 'Local Bank Transfer',
                'description' => 'Nigerian Bank Accounts',
                'icon' => 'fas fa-university',
                'color' => 'emerald',
                'is_active' => true,
                'config' => [
                    'bank_name' => 'GTBank / Access Bank',
                    'account_number' => '0123456789',
                    'account_name' => 'FOOTBALL APP ENT.',
                    'instructions' => 'Transfer exact amount and upload proof'
                ],
                'sort_order' => 9
            ]
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['name' => $method['name'], 'crypto_type' => $method['crypto_type'] ?? null],
                $method
            );
        }
    }
}
