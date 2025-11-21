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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('subscription_type', ['free', 'vip', 'vvip'])->default('free');
            $table->timestamp('vip_expires_at')->nullable();
            $table->timestamp('vvip_expires_at')->nullable();
            $table->boolean('is_vip_active')->default(false);
            $table->boolean('is_vvip_active')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_type', 'vip_expires_at', 'vvip_expires_at', 'is_vip_active', 'is_vvip_active']);
        });
    }
};
