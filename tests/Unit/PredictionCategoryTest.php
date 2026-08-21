<?php

namespace Tests\Unit;

use App\Models\PredictionCategory;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionCategoryTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_category_fillable_and_casts_work(): void
    {
        $category = PredictionCategory::create([
            'name' => 'Over 2.5',
            'slug' => 'over-2-5',
            'code' => 'over_2_5',
            'enabled' => true,
            'min_confidence' => 75,
            'homepage_enabled' => false,
            'sort_order' => 3,
        ]);

        $this->assertTrue($category->enabled);
        $this->assertFalse($category->homepage_enabled);
        $this->assertSame(75, $category->min_confidence);
        $this->assertSame(3, $category->sort_order);
    }

    public function test_category_scopes(): void
    {
        PredictionCategory::create(['name' => '1X2', 'slug' => '1x2', 'code' => '1x2', 'enabled' => true]);
        PredictionCategory::create(['name' => 'Draw', 'slug' => 'draw', 'code' => 'draw', 'enabled' => false]);

        $this->assertSame(1, PredictionCategory::enabled()->count());
        $this->assertSame(1, PredictionCategory::where('code', 'draw')->count());
    }
}
