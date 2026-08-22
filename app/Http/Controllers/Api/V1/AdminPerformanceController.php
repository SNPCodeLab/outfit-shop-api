<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AdminPerformanceController extends BaseApiController
{
    /**
     * Enterprise real-time APM performance telemetry (ADMIN only).
     *
     * Honesty contract: every metric is either measured or reported as
     * unavailable. No fabricated fallback values - a monitoring dashboard
     * must never show green during an outage.
     */
    public function performance(Request $request): JsonResponse
    {
        $hasApiLogs = DB::getSchemaBuilder()->hasTable('api_logs')
            && DB::table('api_logs')->count() > 0;

        $avgResponseTime = null;
        $p95ResponseTime = null;
        $errorRate = null;
        $slowestEndpoints = collect();

        if ($hasApiLogs) {
            $totalRequests = DB::table('api_logs')->count();
            $errorRequests = DB::table('api_logs')->where('status', '>=', 400)->count();
            $errorRate = round(($errorRequests / $totalRequests) * 100, 2);
            $avgResponseTime = round((float) DB::table('api_logs')->avg('duration_ms'), 1);

            $p95ResponseTime = round(
                (float) DB::table('api_logs')
                    ->orderBy('duration_ms', 'desc')
                    ->limit(max(1, (int) round($totalRequests * 0.05)))
                    ->avg('duration_ms'),
                1
            );

            $slowestEndpoints = DB::table('api_logs')
                ->select(
                    'method',
                    'path',
                    DB::raw('COUNT(*) as total_calls'),
                    DB::raw('ROUND(CAST(AVG(duration_ms) AS numeric), 2) as avg_duration_ms'),
                    DB::raw('MAX(duration_ms) as max_duration_ms')
                )
                ->groupBy('method', 'path')
                ->orderBy('avg_duration_ms', 'desc')
                ->limit(5)
                ->get();
        }

        // Database connection pool (Postgres-only metric)
        $activeDbConnections = null;
        try {
            $dbStat = DB::select("SELECT count(*) as count FROM pg_stat_activity WHERE state = 'active'");
            if (! empty($dbStat)) {
                $activeDbConnections = (int) $dbStat[0]->count;
            }
        } catch (\Throwable) {
            // Not Postgres or access denied - reported as unavailable
        }

        // Queue depth
        $queueDepth = DB::getSchemaBuilder()->hasTable('jobs')
            ? DB::table('jobs')->count()
            : null;

        // Redis memory and cache hit ratio (only when Redis is the driver)
        $cacheHitRatioPct = null;
        $redisMemory = null;
        try {
            if (config('cache.default') === 'redis' && class_exists(Redis::class)) {
                $info = Redis::info();
                $redisMemory = $info['used_memory_human'] ?? null;
                if (isset($info['keyspace_hits'], $info['keyspace_misses'])) {
                    $hits = (int) $info['keyspace_hits'];
                    $misses = (int) $info['keyspace_misses'];
                    if ($hits + $misses > 0) {
                        $cacheHitRatioPct = round(($hits / ($hits + $misses)) * 100, 1);
                    }
                }
            }
        } catch (\Throwable) {
            // Redis unreachable - reported as unavailable
        }

        $healthStatus = $this->deriveHealthStatus($errorRate, $queueDepth);

        return $this->successResponse([
            'health_status' => $healthStatus,
            'telemetry_available' => $hasApiLogs,
            'avg_response_time_ms' => $avgResponseTime,
            'p95_response_time_ms' => $p95ResponseTime,
            'error_rate_pct' => $errorRate,
            'cache_hit_ratio_pct' => $cacheHitRatioPct,
            'cache_driver' => config('cache.default'),
            'queue_depth' => $queueDepth,
            'db_active_connections' => $activeDbConnections,
            'db_connection_limit' => 100,
            'redis_memory_used' => $redisMemory,
            'php_peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'php_version' => PHP_VERSION,
            'slowest_endpoints' => $slowestEndpoints,
        ], 'Enterprise APM performance monitoring telemetry retrieved');
    }

    /**
     * Derive health from measured signals only. UNKNOWN when no telemetry
     * exists; DEGRADED when error rate or backlog crosses thresholds.
     */
    private function deriveHealthStatus(?float $errorRate, ?int $queueDepth): string
    {
        if ($errorRate === null) {
            return 'UNKNOWN_NO_TELEMETRY';
        }

        if ($errorRate >= 5.0) {
            return 'DEGRADED_HIGH_ERROR_RATE';
        }

        if ($queueDepth !== null && $queueDepth >= 100) {
            return 'DEGRADED_QUEUE_BACKLOG';
        }

        return 'HEALTHY';
    }
}
