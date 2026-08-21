<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_gate_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prediction_category_id');
            $table->string('market_code');
            $table->string('action'); // approved | rejected | applied
            $table->integer('old_probability')->nullable();
            $table->integer('new_probability')->nullable();
            $table->integer('old_confidence')->nullable();
            $table->integer('new_confidence')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('prediction_category_id')->references('id')->on('prediction_categories')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
            $table->index('market_code', 'gate_audit_market_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_gate_audits');
    }
};
