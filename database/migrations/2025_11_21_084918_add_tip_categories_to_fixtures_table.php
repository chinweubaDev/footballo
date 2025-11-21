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
        Schema::table('fixtures', function (Blueprint $table) {
            $table->boolean('is_vip')->default(false)->after('maxodds_tip');
            $table->boolean('is_vvip')->default(false)->after('is_vip');
            $table->boolean('is_surepick')->default(false)->after('is_vvip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn(['is_vip', 'is_vvip', 'is_surepick']);
        });
    }
};
