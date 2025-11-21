<?php

namespace App\Console\Commands;

use App\Models\PaymentMethod;
use Illuminate\Console\Command;

class UpdatePaymentMethods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:update-methods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update payment methods with sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating payment methods with sample data...');

        // Update Bitcoin
        $bitcoin = PaymentMethod::where('name', 'Bitcoin')->first();
        if ($bitcoin) {
            $bitcoin->setConfigValue('address', '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa');
            $bitcoin->setConfigValue('network', 'bitcoin');
            $bitcoin->save();
            $this->info('✓ Bitcoin address updated');
        }

        // Update USDT
        $usdt = PaymentMethod::where('name', 'Tether')->first();
        if ($usdt) {
            $usdt->setConfigValue('address', 'TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE');
            $usdt->setConfigValue('network', 'tron');
            $usdt->save();
            $this->info('✓ USDT address updated');
        }

        // Update Ethereum
        $eth = PaymentMethod::where('name', 'Ethereum')->first();
        if ($eth) {
            $eth->setConfigValue('address', '0x742d35Cc6634C0532925a3b8D4C9db96C4b4d8b6');
            $eth->setConfigValue('network', 'ethereum');
            $eth->save();
            $this->info('✓ Ethereum address updated');
        }

        // Update PayPal
        $paypal = PaymentMethod::where('name', 'PayPal')->first();
        if ($paypal) {
            $paypal->setConfigValue('email', 'payments@footballpredictions.com');
            $paypal->setConfigValue('client_id', 'sample_client_id');
            $paypal->setConfigValue('client_secret', 'sample_client_secret');
            $paypal->save();
            $this->info('✓ PayPal email updated');
        }

        // Update Skrill
        $skrill = PaymentMethod::where('name', 'Skrill')->first();
        if ($skrill) {
            $skrill->setConfigValue('email', 'payments@footballpredictions.com');
            $skrill->setConfigValue('api_key', 'sample_api_key');
            $skrill->save();
            $this->info('✓ Skrill email updated');
        }

        $this->info('Payment methods updated successfully!');
    }
}
