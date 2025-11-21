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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Flutterwave", "PayPal", "Bitcoin", "USDT"
            $table->string('type'); // e.g., "flutterwave", "paypal", "crypto", "skrill"
            $table->string('crypto_type')->nullable(); // e.g., "BTC", "USDT", "ETH", "BNB", "TRX"
            $table->string('display_name'); // e.g., "Flutterwave", "Bitcoin (BTC)", "Tether (USDT)"
            $table->text('description')->nullable(); // e.g., "Cards, Bank Transfer, Mobile Money"
            $table->string('icon')->nullable(); // Font Awesome icon class
            $table->string('color')->nullable(); // Color for UI (e.g., "blue", "orange", "green")
            $table->json('config')->nullable(); // Configuration data (addresses, emails, etc.)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
