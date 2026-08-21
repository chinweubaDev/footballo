<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backtest_runs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            // API-Football league id (null = all leagues).
            $table->unsignedBigInteger('league_id')->nullable();
            $table->integer('season')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            // JSON array of market codes, e.g. ["1x2","over_2_5"].
            $table->json('markets')->nullable();
            $table->integer('min_confidence')->default(0);
            $table->decimal('min_probability', 5, 2)->default(0);
            $table->string('model_version');
            // Full configuration snapshot for reproducibility.
            $table->json('config_snapshot')->nullable();
            $table->string('status')->default('queued'); // queued|running|completed|failed|cancelled
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

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index('status', 'backtest_runs_status_index');
            $table->index('model_version', 'backtest_runs_model_version_index');
            $table->index('league_id', 'backtest_runs_league_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backtest_runs');
    }
};
