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
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['vip', 'vvip']);
            $table->string('fixture_id')->nullable(); // Reference to fixture if applicable
            $table->string('league_name')->nullable();
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->date('match_date')->nullable();
            $table->time('match_time')->nullable();
            $table->string('prediction')->nullable(); // e.g., "Home Win", "Over 2.5", "Both Teams to Score"
            $table->decimal('odds', 8, 2)->nullable();
            $table->enum('status', ['pending', 'won', 'lost', 'void'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
