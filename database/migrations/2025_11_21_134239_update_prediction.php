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
        Schema::table('predictions', function (Blueprint $table) {
            $table->text('today_tip_content')->nullable()->after('analysis');
            $table->text('featured_tip_content')->nullable()->after('today_tip_content');
            $table->text('vip_tip_content')->nullable()->after('featured_tip_content');
            $table->text('vvip_tip_content')->nullable()->after('vip_tip_content');
            $table->text('surepick_tip_content')->nullable()->after('vvip_tip_content');
            $table->text('maxodds_tip_content')->nullable()->after('surepick_tip_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn([
                'today_tip_content',
                'featured_tip_content',
                'vip_tip_content',
                'vvip_tip_content',
                'surepick_tip_content',
                'maxodds_tip_content'
            ]);
        });
    }
};
