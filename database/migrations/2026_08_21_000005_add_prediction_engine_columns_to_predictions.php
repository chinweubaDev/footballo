<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->string('market_code')->nullable();
            $table->string('selection')->nullable();
            $table->decimal('probability', 5, 2)->nullable();
            $table->string('model_version')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('original_selection')->nullable();
            $table->string('admin_selection')->nullable();
            $table->text('override_reason')->nullable();
            $table->unsignedBigInteger('overridden_by')->nullable();
            $table->dateTime('overridden_at')->nullable();
            $table->boolean('featured')->default(false);
            $table->integer('featured_priority')->default(0);
            $table->dateTime('featured_until')->nullable();
            $table->boolean('admin_featured')->default(false);
            $table->dateTime('locked_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->text('explanation')->nullable();
            $table->string('explanation_status')->nullable();
            $table->unsignedBigInteger('league_id')->nullable();
            $table->integer('data_quality_score')->nullable();
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->foreign('model_id')->references('id')->on('prediction_models')->nullOnDelete();
            $table->foreign('overridden_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('league_id')->references('api_football_league_id')->on('leagues')->nullOnDelete();
        });

        // Backfill model_version for legacy rows.
        DB::table('predictions')->whereNull('model_version')->update(['model_version' => 'v1.0.0']);

        // Backfill market_code for legacy rows based on their existing category string.
        DB::table('predictions')->whereNull('market_code')->orderBy('id')->select(['id', 'category'])->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('predictions')->where('id', $row->id)->update([
                    'market_code' => $this->legacyMarketCode($row->category),
                ]);
            }
        });
    }

    protected function legacyMarketCode(?string $category): string
    {
        if ($category === null || trim($category) === '') {
            return '1x2';
        }

        $normalized = strtolower(trim($category));

        if (str_contains($normalized, '1x2')) {
            return '1x2';
        }

        if ($normalized === 'basketball') {
            return 'basketball';
        }

        // Fallback: slugify the legacy category (e.g. "Over/Under" -> "over_under").
        return Str::slug($category, '_');
    }

    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropForeign(['model_id']);
            $table->dropForeign(['overridden_by']);
            $table->dropForeign(['league_id']);
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn([
                'market_code',
                'selection',
                'probability',
                'model_version',
                'model_id',
                'original_selection',
                'admin_selection',
                'override_reason',
                'overridden_by',
                'overridden_at',
                'featured',
                'featured_priority',
                'featured_until',
                'admin_featured',
                'locked_at',
                'published_at',
                'explanation',
                'explanation_status',
                'league_id',
                'data_quality_score',
            ]);
        });
    }
};
