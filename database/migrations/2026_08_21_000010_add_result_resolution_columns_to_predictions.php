<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            if (! Schema::hasColumn('predictions', 'result')) {
                $table->string('result')->nullable()->after('status');
            }

            if (! Schema::hasColumn('predictions', 'actual_score')) {
                $table->string('actual_score')->nullable()->after('result');
            }

            if (! Schema::hasColumn('predictions', 'resolved_at')) {
                $table->dateTime('resolved_at')->nullable()->after('actual_score');
            }

            if (! Schema::hasColumn('predictions', 'model_result')) {
                $table->string('model_result')->nullable()->after('resolved_at');
            }

            if (! Schema::hasColumn('predictions', 'override_result')) {
                $table->string('override_result')->nullable()->after('model_result');
            }

            if (! Schema::hasColumn('predictions', 'void_reason')) {
                $table->string('void_reason')->nullable()->after('override_result');
            }

            if (! Schema::hasColumn('predictions', 'result_corrections')) {
                $table->json('result_corrections')->nullable()->after('void_reason');
            }
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->index('result', 'predictions_result_index');
            $table->index('resolved_at', 'predictions_resolved_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropIndex('predictions_result_index');
            $table->dropIndex('predictions_resolved_at_index');
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn([
                'result_corrections',
                'void_reason',
                'override_result',
                'model_result',
                'resolved_at',
                'actual_score',
                'result',
            ]);
        });
    }
};
