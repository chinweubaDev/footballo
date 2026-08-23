<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1H — store the full prediction pipeline outputs for every backtest
     * prediction: raw ensemble probability, calibrated probability and the
     * calibration version that was applied. Existing `probability` keeps its
     * historical meaning (the probability the model actually bet with).
     */
    public function up(): void
    {
        Schema::table('backtest_predictions', function (Blueprint $table) {
            $table->decimal('raw_probability', 5, 2)->nullable()->after('probability');
            $table->decimal('calibrated_probability', 5, 2)->nullable()->after('raw_probability');
            $table->string('calibration_version')->nullable()->after('calibrated_probability');
        });
    }

    public function down(): void
    {
        Schema::table('backtest_predictions', function (Blueprint $table) {
            $table->dropColumn(['raw_probability', 'calibrated_probability', 'calibration_version']);
        });
    }
};
