<?php

namespace Tests\Feature;

use App\Models\ApiRequestLog;
use App\Models\SystemEvent;
use App\Services\ApiClient;
use App\Services\SystemEventService;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class ApiClientTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();

        config()->set('services.api_football.retry.base_delay', 0);
        config()->set('services.api_football.retry.max_delay', 0);
    }

    protected function client(): ApiClient
    {
        return new ApiClient(new SystemEventService());
    }

    public function test_retries_429_then_succeeds(): void
    {
        Http::fake([
            'api.example.com/*' => Http::sequence()
                ->push(['error' => 'rate limited'], 429, ['x-ratelimit-remaining' => '0'])
                ->push(['response' => ['ok' => true]], 200, ['x-ratelimit-remaining' => '99']),
        ]);

        $result = $this->client()->get('https://api.example.com/fixtures', ['date' => '2026-08-22'], ['x-rapidapi-key' => 'k']);

        $this->assertSame(['ok' => true], $result['response']);
        $this->assertSame(1, ApiRequestLog::where('is_rate_limited', true)->count());
        $this->assertSame(1, ApiRequestLog::where('successful', true)->count());
    }

    public function test_retries_transient_500_then_succeeds(): void
    {
        Http::fake([
            'api.example.com/*' => Http::sequence()
                ->push(['error' => 'boom'], 500)
                ->push(['ok' => true], 200),
        ]);

        $result = $this->client()->get('https://api.example.com/fixtures');

        $this->assertSame(['ok' => true], $result);
    }

    public function test_does_not_retry_permanent_404(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['error' => 'not found'], 404)]);

        $this->client()->get('https://api.example.com/fixtures');

        // Only one attempt logged (no retries).
        $this->assertSame(1, ApiRequestLog::count());
        $this->assertSame(0, ApiRequestLog::first()->retries);
    }

    public function test_persistent_failure_returns_null_and_creates_alert(): void
    {
        Http::fake(['api.example.com/*' => Http::response(['error' => 'unavailable'], 503)]);

        $result = $this->client()->get('https://api.example.com/fixtures');

        $this->assertNull($result);
        $this->assertTrue(SystemEvent::where('type', 'api_failure')->exists());
    }
}
