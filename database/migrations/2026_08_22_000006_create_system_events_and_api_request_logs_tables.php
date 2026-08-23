<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_events', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('severity')->default('INFO'); // INFO|WARNING|ERROR|CRITICAL
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('type', 'system_events_type_index');
            $table->index('severity', 'system_events_severity_index');
            $table->index('resolved_at', 'system_events_resolved_index');
        });

        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint')->nullable();
            $table->integer('status')->nullable();
            $table->boolean('successful')->default(false);
            $table->boolean('is_rate_limited')->default(false);
            $table->integer('remaining_quota')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->integer('retries')->default(0);
            $table->string('error')->nullable();
            $table->timestamps();

            $table->index('created_at', 'api_request_logs_created_index');
            $table->index('successful', 'api_request_logs_success_index');
            $table->index('is_rate_limited', 'api_request_logs_429_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('system_events');
    }
};
