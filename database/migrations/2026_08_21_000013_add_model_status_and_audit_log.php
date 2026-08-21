<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prediction_models', function (Blueprint $table) {
            if (! Schema::hasColumn('prediction_models', 'status')) {
                // candidate | shadow | approved | active | rejected | retired
                $table->string('status')->default('candidate')->after('active');
            }
        });

        Schema::create('model_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prediction_model_id');
            $table->string('action'); // created | approved | rejected | activated | retired | shadow_started
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('prediction_model_id')->references('id')->on('prediction_models')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
            $table->index('prediction_model_id', 'model_audit_model_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_audit_logs');

        Schema::table('prediction_models', function (Blueprint $table) {
            if (Schema::hasColumn('prediction_models', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
