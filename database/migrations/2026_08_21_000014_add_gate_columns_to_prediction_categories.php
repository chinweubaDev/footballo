<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prediction_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('prediction_categories', 'min_probability')) {
                // null = fall back to the global prediction.no_bet.min_probability.
                $table->integer('min_probability')->nullable()->after('min_confidence');
            }

            if (! Schema::hasColumn('prediction_categories', 'minimum_sample_size')) {
                $table->integer('minimum_sample_size')->default(100)->after('min_probability');
            }

            if (! Schema::hasColumn('prediction_categories', 'gate_status')) {
                // none | recommended | approved | rejected
                $table->string('gate_status')->default('none')->after('minimum_sample_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prediction_categories', function (Blueprint $table) {
            foreach (['min_probability', 'minimum_sample_size', 'gate_status'] as $column) {
                if (Schema::hasColumn('prediction_categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
