<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates a minimal in-memory schema for Phase 1A tests.
 *
 * This intentionally mirrors the NEW tables/columns introduced in Phase 1A so
 * model casts, relationships, scopes and seeders can be tested without running
 * the full production migration set (which contains a MySQL-only fulltext index).
 */
trait InteractsWithPredictionSchema
{
    protected function migratePhase1ASchema(): void
    {
        $this->dropPhase1ATables();

        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_football_league_id')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country')->nullable();
            $table->string('logo')->nullable();
            $table->integer('season')->default(2025);
            $table->boolean('enabled')->default(true);
            $table->boolean('prediction_enabled')->default(true);
            $table->boolean('homepage_enabled')->default(true);
            $table->integer('priority')->default(0);
            $table->integer('prediction_min_confidence')->default(75);
            $table->boolean('auto_publish')->default(true);
            $table->timestamps();
        });

        Schema::create('prediction_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->boolean('enabled')->default(true);
            $table->integer('min_confidence')->default(75);
            $table->boolean('homepage_enabled')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('prediction_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version')->unique();
            $table->text('description')->nullable();
            $table->json('configuration')->nullable();
            $table->boolean('active')->default(false);
            $table->string('status')->default('candidate');
            $table->timestamps();
        });

        Schema::create('model_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prediction_model_id');
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->timestamps();
        });

        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_fixture_id')->unique();
            $table->integer('league_id')->nullable();
            $table->string('league_name');
            $table->string('home_team');
            $table->string('away_team');
            $table->integer('home_team_id')->nullable();
            $table->integer('away_team_id')->nullable();
            $table->integer('season')->nullable();
            $table->string('home_team_logo')->nullable();
            $table->string('away_team_logo')->nullable();
            $table->string('league_logo')->nullable();
            $table->string('league_country')->nullable();
            $table->string('league_flag')->nullable();
            $table->string('status')->default('NS');
            $table->integer('home_goals')->nullable();
            $table->integer('away_goals')->nullable();
            $table->dateTime('match_date')->nullable();
            $table->string('slug')->nullable();
            $table->integer('elapsed')->nullable();
            $table->timestamps();
        });

        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id')->nullable();
            $table->string('category')->nullable();
            $table->string('tip')->nullable();
            $table->integer('confidence')->nullable();
            $table->decimal('odds', 8, 2)->nullable();
            $table->text('analysis')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_maxodds')->default(false);
            $table->string('status')->default('pending');
            $table->string('market_code')->nullable();
            $table->string('selection')->nullable();
            $table->decimal('probability', 5, 2)->nullable();
            $table->string('model_version')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('original_selection')->nullable();
            $table->string('admin_selection')->nullable();
            $table->text('override_reason')->nullable();
            $table->unsignedBigInteger('overridden_by')->nullable();
            $table->dateTime('overridden_at')->nullable();
            $table->boolean('featured')->default(false);
            $table->integer('featured_priority')->default(0);
            $table->dateTime('featured_until')->nullable();
            $table->boolean('admin_featured')->default(false);
            $table->dateTime('locked_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->text('explanation')->nullable();
            $table->string('explanation_status')->nullable();
            $table->unsignedBigInteger('league_id')->nullable();
            $table->integer('data_quality_score')->nullable();
            $table->json('prediction_data')->nullable();
            $table->string('result')->nullable();
            $table->string('actual_score')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->string('model_result')->nullable();
            $table->string('override_result')->nullable();
            $table->string('void_reason')->nullable();
            $table->json('result_corrections')->nullable();
            $table->timestamps();
        });

        Schema::create('prediction_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id')->nullable();
            $table->unsignedBigInteger('prediction_id')->nullable();
            $table->string('action');
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('prediction_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prediction_id')->nullable();
            $table->string('original_selection')->nullable();
            $table->string('new_selection')->nullable();
            $table->decimal('original_probability', 5, 2)->nullable();
            $table->decimal('new_probability', 5, 2)->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
        });

        Schema::create('prediction_features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prediction_id')->nullable();
            $table->unsignedBigInteger('fixture_id')->nullable();
            $table->string('model_version')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('prediction_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id')->nullable();
            $table->string('market_code')->nullable();
            $table->string('model_version')->nullable();
            $table->string('period')->nullable();
            $table->dateTime('period_start')->nullable();
            $table->dateTime('period_end')->nullable();
            $table->integer('total')->default(0);
            $table->integer('won')->default(0);
            $table->integer('lost')->default(0);
            $table->integer('void')->default(0);
            $table->decimal('accuracy', 6, 2)->nullable();
            $table->decimal('roi', 8, 2)->nullable();
            $table->decimal('yield', 8, 2)->nullable();
            $table->decimal('avg_confidence', 6, 2)->nullable();
            $table->decimal('calibration_error', 8, 4)->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('backtest_runs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('league_id')->nullable();
            $table->integer('season')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->json('markets')->nullable();
            $table->integer('min_confidence')->default(0);
            $table->decimal('min_probability', 5, 2)->default(0);
            $table->string('model_version');
            $table->json('config_snapshot')->nullable();
            $table->string('status')->default('queued');
            $table->integer('total_fixtures')->default(0);
            $table->integer('processed_fixtures')->default(0);
            $table->integer('generated_predictions')->default(0);
            $table->integer('resolved_predictions')->default(0);
            $table->json('metrics')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('backtest_predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('backtest_run_id');
            $table->unsignedBigInteger('fixture_id');
            $table->string('market_code');
            $table->string('selection')->nullable();
            $table->decimal('probability', 5, 2)->nullable();
            $table->integer('confidence')->nullable();
            $table->string('model_version')->nullable();
            $table->integer('data_quality_score')->nullable();
            $table->json('prediction_data')->nullable();
            $table->string('status')->nullable();
            $table->string('result')->nullable();
            $table->string('actual_score')->nullable();
            $table->timestamp('predicted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    protected function dropPhase1ATables(): void
    {
        Schema::dropIfExists('backtest_predictions');
        Schema::dropIfExists('backtest_runs');
        Schema::dropIfExists('prediction_performance');
        Schema::dropIfExists('prediction_features');
        Schema::dropIfExists('prediction_overrides');
        Schema::dropIfExists('prediction_logs');
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('fixtures');
        Schema::dropIfExists('model_audit_logs');
        Schema::dropIfExists('users');
        Schema::dropIfExists('prediction_models');
        Schema::dropIfExists('prediction_categories');
        Schema::dropIfExists('leagues');
    }
}
