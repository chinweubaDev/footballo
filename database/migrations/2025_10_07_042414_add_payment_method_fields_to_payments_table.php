<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('crypto_type')->nullable();
            $table->string('crypto_address')->nullable();
            $table->decimal('crypto_amount', 18, 8)->nullable();
            $table->string('paypal_order_id')->nullable();
            $table->string('skrill_transaction_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'crypto_type',
                'crypto_address',
                'crypto_amount',
                'paypal_order_id',
                'skrill_transaction_id'
            ]);
        });
    }
};
