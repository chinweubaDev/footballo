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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained()->onDelete('cascade');
            $table->string('category'); // 1X2, Over/Under, Both Teams to Score, etc.
            $table->string('tip'); // 1, X, 2, Over 2.5, Under 2.5, etc.
            $table->integer('confidence'); // 1-100
            $table->decimal('odds', 8, 2)->nullable();
            $table->text('analysis')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_maxodds')->default(false);
            $table->string('status')->default('pending'); // pending, won, lost, void
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
