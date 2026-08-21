<?php

namespace Tests\Feature;

use App\Models\League;
use App\Models\PredictionCategory;
use App\Models\User;
use App\Services\Prediction\Admin\LeaguePredictionSettingsService;
use App\Services\Prediction\Admin\MarketPredictionSettingsService;
use Database\Seeders\PredictionCategorySeeder;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class PredictionSettingsTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
        $this->seed(PredictionCategorySeeder::class);
    }

    protected function makeAdmin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret', 'is_admin' => true]);
    }

    public function test_league_toggle_preserves_existing_data(): void
    {
        $league = League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
        ]);

        $service = new LeaguePredictionSettingsService();
        $service->toggleEnabled($league, $this->makeAdmin());

        $this->assertFalse($league->fresh()->enabled);
        $this->assertSame(39, $league->fresh()->api_football_league_id);
        $this->assertSame('Premier League', $league->fresh()->name);
    }

    public function test_league_settings_update(): void
    {
        $league = League::create([
            'api_football_league_id' => 39,
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'enabled' => true,
            'prediction_enabled' => true,
            'prediction_min_confidence' => 75,
        ]);

        $service = new LeaguePredictionSettingsService();
        $service->update($league, [
            'prediction_min_confidence' => 80,
            'priority' => 1,
            'auto_publish' => false,
            'homepage_enabled' => false,
        ], $this->makeAdmin());

        $league->refresh();

        $this->assertSame(80, $league->prediction_min_confidence);
        $this->assertSame(1, $league->priority);
        $this->assertFalse($league->auto_publish);
        $this->assertFalse($league->homepage_enabled);
    }

    public function test_market_toggle_and_settings(): void
    {
        $market = PredictionCategory::where('code', 'over_2_5')->first();
        $admin = $this->makeAdmin();

        $service = new MarketPredictionSettingsService();
        $service->toggleEnabled($market, $admin);
        $this->assertFalse($market->fresh()->enabled);

        $service->toggleEnabled($market, $admin);
        $this->assertTrue($market->fresh()->enabled);

        $service->update($market, ['min_confidence' => 80, 'sort_order' => 10], $admin);
        $market->refresh();

        $this->assertSame(80, $market->min_confidence);
        $this->assertSame(10, $market->sort_order);
    }
}
