<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1G.2 publication candidates (League x Market x Model).
     *
     * This is a NEW workflow table — it does NOT duplicate predictions or
     * backtest_predictions. Metrics are snapshotted at mark-time so an
     * approval decision stays reproducible even if the underlying validation
     * data changes later.
     */
    public function up(): void
    {
        Schema::create('publication_candidates', function (Blueprint $table) {
            $table->id();
            // API-Football league id.
            $table->unsignedBigInteger('league_id');
            $table->string('market_code');
            $table->string('model_version');
            $table->string('status')->default('candidate'); // candidate|approved|rejected
            $table->integer('recommended_probability')->nullable();
            $table->integer('recommended_confidence')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'market_code', 'model_version'], 'pub_candidate_league_market_model_unique');
            $table->index('status', 'publication_candidates_status_index');
            $table->index('model_version', 'publication_candidates_model_version_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_candidates');
    }
};
