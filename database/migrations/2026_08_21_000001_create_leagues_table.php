<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            $table->index(['enabled', 'prediction_enabled']);
            $table->index(['enabled', 'homepage_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leagues');
    }
};
