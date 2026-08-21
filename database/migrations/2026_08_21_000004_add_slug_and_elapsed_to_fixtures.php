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
        Schema::table('fixtures', function (Blueprint $table) {
            if (! Schema::hasColumn('fixtures', 'slug')) {
                $table->string('slug')->nullable();
            }
            if (! Schema::hasColumn('fixtures', 'elapsed')) {
                $table->integer('elapsed')->nullable();
            }
        });

        // Backfill unique slugs for any existing fixtures before adding the unique index.
        $used = [];
        DB::table('fixtures')->orderBy('id')->select(['id', 'home_team', 'away_team'])->chunkById(200, function ($fixtures) use (&$used) {
            foreach ($fixtures as $fixture) {
                $base = Str::slug(trim($fixture->home_team).' vs '.trim($fixture->away_team));
                if ($base === '') {
                    $base = 'fixture-'.$fixture->id;
                }

                $slug = $base;
                $suffix = 1;
                while (isset($used[$slug])) {
                    $suffix++;
                    $slug = $base.'-'.$suffix;
                }
                $used[$slug] = true;

                DB::table('fixtures')->where('id', $fixture->id)->update(['slug' => $slug]);
            }
        });

        Schema::table('fixtures', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'elapsed']);
        });
    }
};
