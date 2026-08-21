<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->index('market_code');
            $table->index('status');
            $table->index('model_version');
            $table->index('published_at');
        });

        // Only add the unique constraint if existing data will not violate it.
        // Duplicate rows are reported, never silently deleted.
        $duplicates = DB::table('predictions')
            ->select('fixture_id', 'market_code', 'model_version', DB::raw('COUNT(*) as cnt'))
            ->groupBy('fixture_id', 'market_code', 'model_version')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            Log::warning('Prediction unique index skipped: duplicate (fixture_id, market_code, model_version) rows exist.', [
                'duplicate_groups' => $duplicates->count(),
            ]);

            return;
        }

        Schema::table('predictions', function (Blueprint $table) {
            $table->unique(['fixture_id', 'market_code', 'model_version'], 'predictions_fixture_market_model_unique');
        });
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropUnique('predictions_fixture_market_model_unique');
            $table->dropIndex(['market_code']);
            $table->dropIndex(['status']);
            $table->dropIndex(['model_version']);
            $table->dropIndex(['published_at']);
        });
    }
};
