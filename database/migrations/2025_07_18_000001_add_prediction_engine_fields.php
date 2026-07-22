<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to fixtures table
        Schema::table('fixtures', function (Blueprint $table) {
            if (!Schema::hasColumn('fixtures', 'prediction_json')) {
                $table->json('prediction_json')->nullable()->after('draw');
            }
            if (!Schema::hasColumn('fixtures', 'confidence_score')) {
                $table->integer('confidence_score')->nullable()->after('prediction_json');
            }
            if (!Schema::hasColumn('fixtures', 'expected_home_goals')) {
                $table->decimal('expected_home_goals', 5, 2)->nullable()->after('confidence_score');
            }
            if (!Schema::hasColumn('fixtures', 'expected_away_goals')) {
                $table->decimal('expected_away_goals', 5, 2)->nullable()->after('expected_home_goals');
            }
            if (!Schema::hasColumn('fixtures', 'sport_type')) {
                $table->string('sport_type')->default('football')->after('expected_away_goals');
            }
        });

        // Add new columns to predictions table
        Schema::table('predictions', function (Blueprint $table) {
            if (!Schema::hasColumn('predictions', 'prediction_data')) {
                $table->json('prediction_data')->nullable()->after('draw_tip_content');
            }
            if (!Schema::hasColumn('predictions', 'result')) {
                $table->string('result')->nullable()->after('status');
            }
            if (!Schema::hasColumn('predictions', 'actual_score')) {
                $table->string('actual_score')->nullable()->after('result');
            }
        });

        // Create a prediction_logs table for tracking
        if (!Schema::hasTable('prediction_logs')) {
            Schema::create('prediction_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
                $table->foreignId('prediction_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('action'); // generated, updated, evaluated, score_changed
                $table->json('data')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_logs');

        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn([
                'prediction_json', 'confidence_score', 
                'expected_home_goals', 'expected_away_goals',
                'sport_type',
            ]);
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['prediction_data', 'result', 'actual_score']);
        });
    }
};
