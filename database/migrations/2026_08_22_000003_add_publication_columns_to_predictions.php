<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1I — publication + provenance columns on live predictions.
     *
     * These mirror the backtest provenance (raw/calibrated probability,
     * calibration version) and add publication-decision provenance so a
     * published prediction remains reproducible after settings change.
     */
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->decimal('raw_probability', 5, 2)->nullable()->after('probability');
            $table->decimal('calibrated_probability', 5, 2)->nullable()->after('raw_probability');
            $table->string('calibration_version')->nullable()->after('calibrated_probability');

            // Publication-decision provenance.
            $table->integer('gate_probability')->nullable()->after('calibration_version');
            $table->integer('gate_confidence')->nullable()->after('gate_probability');
            $table->string('configuration_version')->nullable()->after('gate_confidence');
            $table->string('publication_reason')->nullable()->after('configuration_version');

            // Settlement: model / public / final results are kept separate.
            $table->string('public_result')->nullable()->after('override_result');
            $table->string('settlement_result')->nullable()->after('public_result');
            $table->timestamp('settled_at')->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn([
                'raw_probability',
                'calibrated_probability',
                'calibration_version',
                'gate_probability',
                'gate_confidence',
                'configuration_version',
                'publication_reason',
                'public_result',
                'settlement_result',
                'settled_at',
            ]);
        });
    }
};
