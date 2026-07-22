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
        Schema::table('fixtures', function (Blueprint $table) {
            $table->boolean('over15')->default(false)->after('is_surepick');
            $table->boolean('over25')->default(false)->after('over15');
            $table->boolean('double_chance')->default(false)->after('over25');
            $table->boolean('bts')->default(false)->after('double_chance');
            $table->boolean('draw')->default(false)->after('bts');
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->text('over15_tip_content')->nullable()->after('maxodds_tip_content');
            $table->text('over25_tip_content')->nullable()->after('over15_tip_content');
            $table->text('double_chance_tip_content')->nullable()->after('over25_tip_content');
            $table->text('bts_tip_content')->nullable()->after('double_chance_tip_content');
            $table->text('draw_tip_content')->nullable()->after('bts_tip_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn(['over15', 'over25', 'double_chance', 'bts', 'draw']);
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn([
                'over15_tip_content',
                'over25_tip_content',
                'double_chance_tip_content',
                'bts_tip_content',
                'draw_tip_content'
            ]);
        });
    }
};
