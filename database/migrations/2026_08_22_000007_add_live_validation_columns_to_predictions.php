<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1M — live validation columns.
     *
     * provenance_status  : FeatureProvenanceService outcome recorded at
     *                      settlement time (valid / invalid /
     *                      provenance_uncertain). Invalid rows are excluded
     *                      from model-performance calculations.
     *
     * settlement_status  : 'settled' when a result has been written,
     *                      'pending_review' when the API scoreline is
     *                      ambiguous and must not be settled.
     */
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->string('provenance_status')->nullable()->after('feature_data_timestamp');
            $table->string('settlement_status')->nullable()->after('settled_at');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['provenance_status', 'settlement_status']);
        });
    }
};
