<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            // The publish decision at prediction time (published|generated|no_bet).
            $table->string('status')->nullable();
            $table->string('result')->nullable(); // won|lost|void
            $table->string('actual_score')->nullable();
            // The kickoff timestamp: when the prediction would have been made.
            $table->timestamp('predicted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('backtest_run_id')->references('id')->on('backtest_runs')->cascadeOnDelete();
            $table->foreign('fixture_id')->references('id')->on('fixtures')->cascadeOnDelete();

            $table->unique(['backtest_run_id', 'fixture_id', 'market_code'], 'backtest_pred_run_fixture_market_unique');
            $table->index(['backtest_run_id', 'market_code'], 'backtest_pred_run_market_index');
            $table->index(['backtest_run_id', 'result'], 'backtest_pred_run_result_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backtest_predictions');
    }
};
