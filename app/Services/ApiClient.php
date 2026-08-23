<?php

namespace App\Services;

use App\Models\ApiRequestLog;
use Illuminate\Support\Facades\Http;

/**
 * Phase 1K — retrying API client with exponential backoff and rate-limit
 * awareness. Transient failures (408/429/500/502/503/504/timeouts) are retried
 * with increasing delays; permanent 4xx errors are not retried blindly. Every
 * request is logged for rate-limit/health visibility.
 */
class ApiClient
{
    public function __construct(protected SystemEventService $events)
    {
    }

    /**
     * Perform a GET request and return the decoded JSON body, or null.
     *
     * @param array<string,string> $headers
     * @param array<string,mixed>  $params
     * @return array<string,mixed>|null
     */
    public function get(string $url, array $params = [], array $headers = []): ?array
    {
        $cfg = config('services.api_football.retry', []);
        $maxAttempts = (int) ($cfg['max_attempts'] ?? 4);
        $baseDelay = (int) ($cfg['base_delay'] ?? 1);
        $maxDelay = (int) ($cfg['max_delay'] ?? 30);
        $transient = $cfg['transient_statuses'] ?? [408, 429, 500, 502, 503, 504];

        $delay = $baseDelay;
        $lastStatus = null;
        $lastError = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $start = microtime(true);

            try {
                $response = Http::timeout(25)->withHeaders($headers)->get($url, $params);
                $status = $response->status();
                $remaining = $response->header('x-ratelimit-remaining');
                $remaining = is_numeric($remaining) ? (int) $remaining : null;
                $durationMs = (int) round((microtime(true) - $start) * 1000);

                $success = $response->successful();
                $rateLimited = $status === 429;

                $this->log($url, $status, $success, $rateLimited, $remaining, $durationMs, $attempt - 1, $success ? null : 'http_'.$status);

                if ($success) {
                    return $response->json();
                }

                $lastStatus = $status;
                $lastError = 'http_'.$status;

                // Only retry transient failures.
                if (! in_array($status, $transient, true) || $attempt === $maxAttempts) {
                    break;
                }

                $retryAfter = $status === 429 ? (int) $response->header('retry-after') : 0;

                if ($retryAfter > 0) {
                    sleep(min($retryAfter, $maxDelay));
                } else {
                    sleep($delay);
                    $delay = min($delay * 2, $maxDelay);
                }
            } catch (\Throwable $e) {
                $lastError = 'network_timeout';
                $durationMs = (int) round((microtime(true) - $start) * 1000);

                $this->log($url, null, false, false, null, $durationMs, $attempt - 1, 'network_timeout');

                if ($attempt === $maxAttempts) {
                    break;
                }

                sleep($delay);
                $delay = min($delay * 2, $maxDelay);
            }
        }

        // Persistent failure → alert (but never fabricate data).
        $this->events->apiFailure("API request failed after {$maxAttempts} attempts: {$url}", [
            'url' => $url,
            'last_status' => $lastStatus,
            'last_error' => $lastError,
        ]);

        return null;
    }

    protected function log(
        string $url,
        ?int $status,
        bool $successful,
        bool $rateLimited,
        ?int $remaining,
        int $durationMs,
        int $retries,
        ?string $error,
    ): void {
        ApiRequestLog::create([
            'endpoint' => mb_substr(parse_url($url, PHP_URL_PATH) ?: $url, 0, 255),
            'status' => $status,
            'successful' => $successful,
            'is_rate_limited' => $rateLimited,
            'remaining_quota' => $remaining,
            'duration_ms' => $durationMs,
            'retries' => $retries,
            'error' => $error,
        ]);
    }
}
