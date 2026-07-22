<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── TEAMS ──────────────────────────────────────
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_team_id')->unique();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('country')->nullable();
            $table->boolean('national')->default(false);
            $table->timestamps();
        });

        // ─── PLAYERS ────────────────────────────────────
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_player_id')->unique();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->string('nationality')->nullable();
            $table->integer('age')->nullable();
            $table->string('position')->nullable(); // G, D, M, F
            $table->timestamps();
        });

        // ─── FIXTURE EVENTS (goals, cards, subs) ────────
        Schema::create('fixture_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('assist_player_id')->nullable();
            $table->string('type'); // Goal, Card, subst
            $table->string('detail'); // Normal Goal, Yellow Card, Substitution 1
            $table->integer('elapsed');
            $table->integer('extra')->nullable();
            $table->timestamps();
        });

        // ─── FIXTURE LINEUPS ────────────────────────────
        Schema::create('fixture_lineups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->unsignedBigInteger('team_id');
            $table->string('formation')->nullable();
            $table->json('start_xi')->nullable();   // [{player_id, name, number, pos, grid}]
            $table->json('substitutes')->nullable();
            $table->unsignedBigInteger('coach_id')->nullable();
            $table->string('coach_name')->nullable();
            $table->timestamps();
        });

        // ─── FIXTURE PLAYER STATS ──────────────────────
        Schema::create('fixture_player_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('player_id');
            $table->integer('minutes')->nullable();
            $table->string('position')->nullable();
            $table->string('rating')->nullable();
            $table->integer('shots_total')->nullable();
            $table->integer('shots_on')->nullable();
            $table->integer('goals_scored')->nullable();
            $table->integer('goals_conceded')->nullable();
            $table->integer('assists')->nullable();
            $table->integer('saves')->nullable();
            $table->integer('passes_total')->nullable();
            $table->integer('passes_key')->nullable();
            $table->string('passes_accuracy')->nullable();
            $table->integer('tackles_total')->nullable();
            $table->integer('tackles_blocks')->nullable();
            $table->integer('tackles_interceptions')->nullable();
            $table->integer('duels_total')->nullable();
            $table->integer('duels_won')->nullable();
            $table->integer('dribbles_attempts')->nullable();
            $table->integer('dribbles_success')->nullable();
            $table->integer('fouls_drawn')->nullable();
            $table->integer('fouls_committed')->nullable();
            $table->integer('cards_yellow')->nullable();
            $table->integer('cards_red')->nullable();
            $table->integer('penalty_scored')->nullable();
            $table->integer('penalty_missed')->nullable();
            $table->integer('penalty_saved')->nullable();
            $table->timestamps();

            $table->unique(['fixture_id', 'player_id']);
        });

        // ─── FIXTURE TEAM STATISTICS ────────────────────
        Schema::create('fixture_team_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->unsignedBigInteger('team_id');
            $table->integer('shots_on_goal')->nullable();
            $table->integer('shots_off_goal')->nullable();
            $table->integer('total_shots')->nullable();
            $table->integer('blocked_shots')->nullable();
            $table->integer('shots_insidebox')->nullable();
            $table->integer('shots_outsidebox')->nullable();
            $table->integer('fouls')->nullable();
            $table->integer('corner_kicks')->nullable();
            $table->integer('offsides')->nullable();
            $table->string('ball_possession')->nullable();
            $table->integer('yellow_cards')->nullable();
            $table->integer('red_cards')->nullable();
            $table->integer('goalkeeper_saves')->nullable();
            $table->integer('total_passes')->nullable();
            $table->integer('passes_accurate')->nullable();
            $table->string('passes_pct')->nullable();
            $table->decimal('expected_goals', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['fixture_id', 'team_id']);
        });

        // ─── BETTING ODDS ───────────────────────────────
        Schema::create('betting_odds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->string('bookmaker_name');
            $table->integer('bookmaker_id');
            $table->decimal('home_odds', 8, 2)->nullable();
            $table->decimal('draw_odds', 8, 2)->nullable();
            $table->decimal('away_odds', 8, 2)->nullable();
            $table->decimal('over25_odds', 8, 2)->nullable();
            $table->decimal('under25_odds', 8, 2)->nullable();
            $table->decimal('over15_odds', 8, 2)->nullable();
            $table->decimal('under15_odds', 8, 2)->nullable();
            $table->decimal('bts_yes_odds', 8, 2)->nullable();
            $table->decimal('bts_no_odds', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['fixture_id', 'bookmaker_id']);
        });

        // ─── PREDICTION LOGS (existing table enhanced) ──
        if (!Schema::hasTable('prediction_logs')) {
            Schema::create('prediction_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
                $table->foreignId('prediction_id')->nullable()->constrained('predictions')->cascadeOnDelete();
                $table->string('action');
                $table->json('data')->nullable();
                $table->timestamps();
            });
        }

        // ─── Add missing columns to fixtures ────────────
        Schema::table('fixtures', function (Blueprint $table) {
            if (!Schema::hasColumn('fixtures', 'sport_type')) {
                $table->string('sport_type')->default('football');
            }
            if (!Schema::hasColumn('fixtures', 'confidence_score')) {
                $table->integer('confidence_score')->nullable();
            }
            if (!Schema::hasColumn('fixtures', 'expected_home_goals')) {
                $table->decimal('expected_home_goals', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('fixtures', 'expected_away_goals')) {
                $table->decimal('expected_away_goals', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('fixtures', 'prediction_json')) {
                $table->json('prediction_json')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('betting_odds');
        Schema::dropIfExists('fixture_team_stats');
        Schema::dropIfExists('fixture_player_stats');
        Schema::dropIfExists('fixture_lineups');
        Schema::dropIfExists('fixture_events');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
    }
};
