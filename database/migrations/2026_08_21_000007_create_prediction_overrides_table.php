<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->constrained('predictions')->cascadeOnDelete();
            $table->string('original_selection')->nullable();
            $table->string('new_selection')->nullable();
            $table->decimal('original_probability', 5, 2)->nullable();
            $table->decimal('new_probability', 5, 2)->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('prediction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_overrides');
    }
};
