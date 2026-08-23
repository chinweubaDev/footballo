<?php

namespace App\Services;

use App\Models\SystemEvent;

/**
 * Phase 1K — persistent system events / alerts.
 */
class SystemEventService
{
    public function record(
        string $type,
        string $message,
        string $severity = SystemEvent::SEVERITY_INFO,
        ?array $context = null,
    ): SystemEvent {
        return SystemEvent::create([
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public function apiFailure(string $message, ?array $context = null): SystemEvent
    {
        return $this->record('api_failure', $message, SystemEvent::SEVERITY_ERROR, $context);
    }

    public function rateLimitExhaustion(string $message, ?array $context = null): SystemEvent
    {
        return $this->record('rate_limit_exhaustion', $message, SystemEvent::SEVERITY_WARNING, $context);
    }

    public function generationFailure(string $message, ?array $context = null): SystemEvent
    {
        return $this->record('generation_failure', $message, SystemEvent::SEVERITY_ERROR, $context);
    }

    public function settlementFailure(string $message, ?array $context = null): SystemEvent
    {
        return $this->record('settlement_failure', $message, SystemEvent::SEVERITY_ERROR, $context);
    }

    public function ambiguousResult(string $message, ?array $context = null): SystemEvent
    {
        return $this->record('ambiguous_result', $message, SystemEvent::SEVERITY_WARNING, $context);
    }

    public function provenanceFailure(string $message, ?array $context = null): SystemEvent
    {
        return $this->record('provenance_failure', $message, SystemEvent::SEVERITY_WARNING, $context);
    }

    /**
     * Record a pipeline stage completion for the pipeline-health dashboard.
     */
    public function pipelineRun(string $stage, bool $success, ?string $message = null, ?array $context = null): SystemEvent
    {
        return $this->record(
            "pipeline.{$stage}",
            $message ?? ($success ? 'completed' : 'failed'),
            $success ? SystemEvent::SEVERITY_INFO : SystemEvent::SEVERITY_ERROR,
            $context,
        );
    }
}
