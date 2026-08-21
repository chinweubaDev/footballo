<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_categories');
    }
};
