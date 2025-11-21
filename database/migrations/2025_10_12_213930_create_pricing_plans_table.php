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
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "3 Days", "1 Week", "1 Month"
            $table->string('key')->unique(); // e.g., "3_days", "1_week", "1_month"
            $table->decimal('price_usd', 10, 2); // Base price in USD
            $table->decimal('price_ngn', 10, 2)->nullable(); // Price in Nigerian Naira
            $table->decimal('price_kes', 10, 2)->nullable(); // Price in Kenyan Shilling
            $table->decimal('price_ghs', 10, 2)->nullable(); // Price in Ghanaian Cedi
            $table->decimal('price_zwl', 10, 2)->nullable(); // Price in Zimbabwean Dollar
            $table->decimal('price_zmw', 10, 2)->nullable(); // Price in Zambian Kwacha
            $table->decimal('price_ugx', 10, 2)->nullable(); // Price in Ugandan Shilling
            $table->decimal('price_tzs', 10, 2)->nullable(); // Price in Tanzanian Shilling
            $table->integer('duration_days'); // Duration in days
            $table->json('features')->nullable(); // Array of features
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
        Schema::dropIfExists('pricing_plans');
    }
};
