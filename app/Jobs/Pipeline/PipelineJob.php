<?php

namespace App\Jobs\Pipeline;

use App\Models\SystemEvent;
use App\Services\SystemEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * Phase 1K — base queued pipeline job.
 *
 * Idempotent (delegates to idempotent commands), retryable (configurable
 * tries/backoff), observable (records a pipeline-health event on success and
 * failure) and alerting (creates a system event after max retries).
 *
 * A pipeline_run_id correlation id links a run across jobs, API requests and
 * system events for debugging.
 */
abstract class PipelineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public string $pipelineRunId;

    public function __construct()
    {
        $this->pipelineRunId = (string) Str::uuid();
    }

    abstract protected function stage(): string;

    protected function command(): string
    {
        return '';
    }

    /** @return array<string,mixed> */
    protected function params(): array
    {
        return [];
    }

    public function handle(): void
    {
        $command = $this->command();
        $startedAt = microtime(true);
        $startedAtTime = now()->toDateTimeString();

        $exit = $command === '' ? 0 : Artisan::call($command, $this->params());

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $output = trim((string) Artisan::output());

        if ($exit !== 0) {
            throw new \RuntimeException("Command '{$command}' exited with code {$exit}.");
        }

        app(SystemEventService::class)->pipelineRun($this->stage(), true, null, [
            'pipeline_run_id' => $this->pipelineRunId,
            'started_at' => $startedAtTime,
            'duration_ms' => $durationMs,
            'output' => mb_substr($output, 0, 2000),
        ]);
    }

    public function failed(?\Throwable $e): void
    {
        $events = app(SystemEventService::class);

        $events->pipelineRun($this->stage(), false, $e?->getMessage(), [
            'pipeline_run_id' => $this->pipelineRunId,
        ]);

        $events->record(
            'job_failure',
            "Pipeline stage '{$this->stage()}' failed: ".($e?->getMessage() ?? 'unknown error'),
            SystemEvent::SEVERITY_ERROR,
            ['stage' => $this->stage(), 'pipeline_run_id' => $this->pipelineRunId],
        );
    }
}
