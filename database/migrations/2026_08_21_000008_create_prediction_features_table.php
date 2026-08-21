<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->nullable()->constrained('predictions')->cascadeOnDelete();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->string('model_version')->nullable();
            $table->json('features')->nullable();
            $table->timestamps();

            $table->index('fixture_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_features');
    }
};
