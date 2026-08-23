<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1I — league-level probability override + league x market gates.
     *
     * Gate precedence (most specific wins):
     *   league + market  >  market  >  league  >  global  >  system default.
     */
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->integer('prediction_min_probability')->nullable()->after('prediction_min_confidence');
        });

        Schema::create('league_market_gates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id'); // API-Football league id
            $table->string('market_code');
            $table->boolean('enabled')->default(true);
            $table->integer('min_probability')->nullable();
            $table->integer('min_confidence')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'market_code'], 'league_market_gate_unique');
            $table->index('market_code', 'league_market_gate_market_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_market_gates');

        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn('prediction_min_probability');
        });
    }
};
