<?php

namespace Database\Seeders;

use App\Models\PredictionCategory;
use Illuminate\Database\Seeder;

class PredictionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => '1X2', 'slug' => '1x2', 'code' => '1x2', 'sort_order' => 1, 'min_confidence' => 75, 'homepage_enabled' => true],
            ['name' => 'Over 1.5', 'slug' => 'over-1-5', 'code' => 'over_1_5', 'sort_order' => 2, 'min_confidence' => 75, 'homepage_enabled' => false],
            ['name' => 'Over 2.5', 'slug' => 'over-2-5', 'code' => 'over_2_5', 'sort_order' => 3, 'min_confidence' => 75, 'homepage_enabled' => false],
            ['name' => 'Double Chance', 'slug' => 'double-chance', 'code' => 'double_chance', 'sort_order' => 4, 'min_confidence' => 75, 'homepage_enabled' => true],
            ['name' => 'BTTS', 'slug' => 'btts', 'code' => 'btts', 'sort_order' => 5, 'min_confidence' => 75, 'homepage_enabled' => false],
            ['name' => 'Draw', 'slug' => 'draw', 'code' => 'draw', 'sort_order' => 6, 'min_confidence' => 75, 'homepage_enabled' => false],
            ['name' => 'Correct Score', 'slug' => 'correct-score', 'code' => 'correct_score', 'sort_order' => 7, 'min_confidence' => 85, 'homepage_enabled' => false],
        ];

        foreach ($categories as $category) {
            PredictionCategory::updateOrCreate(
                ['code' => $category['code']],
                array_merge($category, [
                    'enabled' => true,
                ])
            );
        }
    }
}
