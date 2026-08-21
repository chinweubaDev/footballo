<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            $table->foreign('league_id')->references('api_football_league_id')->on('leagues')->nullOnDelete();
            $table->index(['league_id', 'market_code', 'model_version', 'period'], 'pred_perf_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_performance');
    }
};
