<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiRequestLog;
use App\Models\SystemEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    public function api()
    {
        $today = today();

        $summary = [
            'requests_today' => ApiRequestLog::whereDate('created_at', $today)->count(),
            'successful_today' => ApiRequestLog::whereDate('created_at', $today)->where('successful', true)->count(),
            'failed_today' => ApiRequestLog::whereDate('created_at', $today)->where('successful', false)->count(),
            'rate_limited_today' => ApiRequestLog::whereDate('created_at', $today)->where('is_rate_limited', true)->count(),
            'last_success' => ApiRequestLog::where('successful', true)->latest()->first(),
            'last_failure' => ApiRequestLog::where('successful', false)->latest()->first(),
            'last_quota' => ApiRequestLog::whereNotNull('remaining_quota')->latest()->first(),
        ];

        return view('admin.system.api', [
            'summary' => $summary,
            'recent' => ApiRequestLog::latest()->limit(30)->get(),
        ]);
    }

    public function alerts()
    {
        return view('admin.system.alerts', [
            'alerts' => SystemEvent::where('severity', '!=', SystemEvent::SEVERITY_INFO)
                ->orWhereNull('resolved_at')
                ->latest()
                ->limit(100)
                ->get(),
        ]);
    }

    public function pipeline()
    {
        $stages = [
            'sync_fixtures' => 60,          // minutes — expected cadence
            'generate_predictions' => 720,
            'generate_shadow' => 720,
            'lock_predictions' => 15,
            'update_live_scores' => 10,
            'resolve_results' => 10,
            'aggregate_performance' => 60,
        ];

        $health = [];

        foreach ($stages as $stage => $cadenceMinutes) {
            $latest = SystemEvent::where('type', "pipeline.{$stage}")->latest()->first();

            if ($latest === null) {
                $health[$stage] = ['last_run_at' => null, 'status' => 'UNKNOWN', 'message' => 'Never run'];
                continue;
            }

            $minutesAgo = $latest->created_at->diffInMinutes(now());

            // Heartbeat: stale if more than 3x the expected cadence has elapsed.
            $stale = $minutesAgo > ($cadenceMinutes * 3);

            $context = $latest->context ?? [];

            $health[$stage] = [
                'last_run_at' => $latest->created_at,
                'started_at' => $context['started_at'] ?? null,
                'duration_ms' => $context['duration_ms'] ?? null,
                'output' => $context['output'] ?? null,
                'pipeline_run_id' => $context['pipeline_run_id'] ?? null,
                'status' => $latest->severity === SystemEvent::SEVERITY_ERROR
                    ? 'FAILED'
                    : ($stale ? 'WARNING' : 'SUCCESS'),
                'message' => $stale ? "Stale — last run {$minutesAgo}m ago" : $latest->message,
            ];
        }

        $failedJobs = DB::table('failed_jobs')->latest('failed_at')->limit(30)->get();

        return view('admin.system.pipeline', [
            'health' => $health,
            'failedJobs' => $failedJobs,
        ]);
    }

    /**
     * /admin/system/queue — queue health dashboard.
     */
    public function queue()
    {
        $now = now()->timestamp;
        $config = config('services.queue_health');

        $pending = DB::table('jobs')->whereNull('reserved_at')->where('available_at', '<=', $now)->count();
        $processing = DB::table('jobs')->whereNotNull('reserved_at')->count();
        $failedCount = DB::table('failed_jobs')->count();

        $lastReservedAt = DB::table('jobs')->max('reserved_at');
        $lastFailedAt = DB::table('failed_jobs')->max('failed_at');

        $lastSuccess = SystemEvent::where('type', 'like', 'pipeline.%')
            ->where('severity', SystemEvent::SEVERITY_INFO)
            ->latest()
            ->first();

        $recentCritical = SystemEvent::where('severity', SystemEvent::SEVERITY_ERROR)
            ->where('created_at', '>=', now()->subMinutes((int) $config['critical_window_minutes']))
            ->exists();

        $recentFailedJob = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHours((int) $config['failed_window_hours'])->timestamp)
            ->exists();

        $status = $recentCritical
            ? 'FAILED'
            : ($recentFailedJob || $pending >= (int) $config['pending_warning_threshold'] ? 'WARNING' : 'HEALTHY');

        $failedJobs = DB::table('failed_jobs')->latest('failed_at')->limit(50)->get();

        // Decode job display name from payload for readability.
        $failedJobs = $failedJobs->map(function ($job) {
            $payload = json_decode($job->payload ?? '{}', true);
            $job->display_name = $payload['displayName'] ?? 'unknown';
            $job->failed_at_dt = $job->failed_at ? now()->setTimestamp($job->failed_at) : null;

            return $job;
        });

        return view('admin.system.queue', [
            'connection' => config('queue.default'),
            'status' => $status,
            'pending' => $pending,
            'processing' => $processing,
            'failedCount' => $failedCount,
            'failedJobs' => $failedJobs,
            'lastSuccess' => $lastSuccess,
            'lastWorkerActivityAt' => max($lastReservedAt, $lastFailedAt, $lastSuccess?->created_at?->timestamp ?? 0),
            'thresholds' => $config,
        ]);
    }

    public function retryFailedJob(Request $request, string $id)
    {
        Artisan::call('queue:retry', ['id' => [$id]]);

        return back()->with('success', "Job {$id} pushed back onto the queue.");
    }

    public function forgetFailedJob(Request $request, string $id)
    {
        Artisan::call('queue:forget', ['id' => $id]);

        return back()->with('success', "Failed job {$id} removed from the failed table.");
    }
}
