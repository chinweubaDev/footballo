<?php

namespace Tests\Feature;

use App\Models\SystemEvent;
use App\Services\SystemEventService;
use Tests\Concerns\InteractsWithPredictionSchema;
use Tests\TestCase;

class SystemEventServiceTest extends TestCase
{
    use InteractsWithPredictionSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migratePhase1ASchema();
    }

    public function test_records_event_with_severity(): void
    {
        $event = (new SystemEventService())->record('api_failure', 'boom', SystemEvent::SEVERITY_ERROR, ['endpoint' => '/fixtures']);

        $this->assertSame('api_failure', $event->type);
        $this->assertSame(SystemEvent::SEVERITY_ERROR, $event->severity);
        $this->assertSame(['endpoint' => '/fixtures'], $event->context);
        $this->assertNull($event->resolved_at);
    }

    public function test_pipeline_run_success_and_failure(): void
    {
        $service = new SystemEventService();

        $service->pipelineRun('sync_fixtures', true);
        $service->pipelineRun('sync_fixtures', false, 'network timeout');

        $events = SystemEvent::where('type', 'pipeline.sync_fixtures')->orderBy('id')->get();

        $this->assertSame(SystemEvent::SEVERITY_INFO, $events[0]->severity);
        $this->assertSame(SystemEvent::SEVERITY_ERROR, $events[1]->severity);
        $this->assertSame('network timeout', $events[1]->message);
    }
}
