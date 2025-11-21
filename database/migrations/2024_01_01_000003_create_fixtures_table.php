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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->integer('api_fixture_id')->unique();
            $table->string('league_name');
            $table->string('league_country');
            $table->string('league_logo')->nullable();
            $table->string('league_flag')->nullable();
            $table->integer('league_id');
            $table->integer('season');
            $table->string('round')->nullable();
            $table->string('home_team');
            $table->string('away_team');
            $table->string('home_team_logo')->nullable();
            $table->string('away_team_logo')->nullable();
            $table->integer('home_team_id');
            $table->integer('away_team_id');
            $table->datetime('match_date');
            $table->string('venue_name')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('status')->default('scheduled');
            $table->integer('home_goals')->nullable();
            $table->integer('away_goals')->nullable();
            $table->integer('home_goals_halftime')->nullable();
            $table->integer('away_goals_halftime')->nullable();
            $table->boolean('today_tip')->default(false);
            $table->boolean('featured')->default(false);
            $table->boolean('maxodds_tip')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
