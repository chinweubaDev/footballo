<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1J — live shadow validation provenance.
     *
     * prediction_generated_at : when the prediction was generated (leak audit).
     * feature_data_timestamp  : timestamp of the input data used to build features.
     * data_quality_flags      : structured missing-feature flags (never inflates quality).
     */
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->timestamp('prediction_generated_at')->nullable()->after('published_at');
            $table->timestamp('feature_data_timestamp')->nullable()->after('prediction_generated_at');
            $table->json('data_quality_flags')->nullable()->after('data_quality_score');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['prediction_generated_at', 'feature_data_timestamp', 'data_quality_flags']);
        });
    }
};
