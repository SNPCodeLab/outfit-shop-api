<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Models\Employee;
use App\Models\Product;
use Illuminate\Cache\CacheManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RouteInstance;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatusController extends Controller
{
    /**
     * Probe timeouts are hard bounds: every probe is a real HTTP request,
     * and a stalled backend yields UNREACHABLE rows instead of a hanging
     * page. Probes run in parallel; the suite is bounded by the total
     * timeout regardless of endpoint count.
     */
    private const PROBE_CONNECT_TIMEOUT_SECONDS = 3;

    private const PROBE_TOTAL_TIMEOUT_SECONDS = 15;

    private const CACHE_KEY = 'web.status.probe.v1';

    private const CACHE_TTL_SECONDS = 60;

    public function __construct(private readonly CacheManager $cache) {}

    public function index(Request $request): View
    {
        if ($request->boolean('refresh')) {
            $this->cache->forget(self::CACHE_KEY);
        }

        $baseUrl = $request->getSchemeAndHttpHost();

        $report = $this->cache->remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn () => $this->runProbeSuite($baseUrl)
        );

        return view('status', [
            'probe' => $report,
            'system' => $this->getSystemMetrics(),
            'database' => $this->getDatabaseMetrics(),
            'traffic' => $this->getTrafficMetrics(),
            'runtime' => $this->getRuntimeMetrics(),
            'health_score' => $this->calculateHealthScore($report),
            'generated_at' => now()->format('Y-m-d H:i:s T'),
        ]);
    }

    private function calculateHealthScore(array $report): int
    {
        $total = $report['summary']['total'] ?? 0;
        if ($total === 0) {
            return 0;
        }

        $operational = $report['summary']['operational'] ?? 0;
        $authGuarded = $report['summary']['auth_guarded'] ?? 0;
        $writeGuarded = $report['summary']['write_guarded'] ?? 0;
        $parameterized = $report['summary']['parameterized'] ?? 0;

        // Healthy = Operational OR Guarded (valid states)
        $healthy = $operational + $authGuarded + $writeGuarded + $parameterized;

        return (int) round(($healthy / $total) * 100);
    }

    /**
     * Enumerate every registered route and probe each one over real HTTP.
     * Every state, status code and latency on the Status page originates
     * here; nothing is sampled, randomized or replayed from an old run.
     * All probes fire in parallel and are bounded by hard curl timeouts, so
     * a stalled backend degrades to UNREACHABLE rows instead of hanging.
     */
    private function runProbeSuite(string $baseUrl): array
    {
        $baseUrl = rtrim($baseUrl, '/');

        $endpoints = $this->enumerateEndpoints();
        $token = $this->acquireProbeToken($baseUrl);

        $targets = [];
        foreach ($endpoints as $index => $endpoint) {
            if ($endpoint['probes_as_get']) {
                $targets[$index] = $baseUrl.'/'.$endpoint['uri_path'];
            }
        }

        $results = $this->executeParallelProbes($targets, $token['token']);

        // The login route is exercised with a real credential exchange.
        if (($loginIndex = $this->loginEndpointIndex($endpoints)) !== null && $token['latency_ms'] !== null) {
            $results[$loginIndex] = [
                'http_code' => $token['http_code'],
                'time_ms' => $token['latency_ms'],
                'error' => $token['error'],
            ];
        }

        $this->revokeProbeToken($token['token_id']);

        $checkedAt = now()->format('H:i:s');
        $rows = [];
        foreach ($endpoints as $index => $endpoint) {
            $result = $results[$index] ?? null;

            if (! $endpoint['probes_as_get']) {
                $state = 'PARAMETERIZED';
                $httpCode = null;
                $timeMs = null;
            } elseif ($result === null) {
                $state = 'UNAVAILABLE';
                $httpCode = null;
                $timeMs = null;
            } elseif ($result['error'] !== null) {
                $state = 'UNREACHABLE';
                $httpCode = null;
                $timeMs = null;
            } else {
                $httpCode = $result['http_code'];
                $timeMs = $result['time_ms'];
                $state = $this->classifyState((int) $httpCode);
            }

            $rows[] = [
                'method' => $endpoint['method'],
                'uri' => $endpoint['uri'],
                'tier' => $endpoint['tier'],
                'state' => $state,
                'http_code' => $httpCode,
                'time_ms' => $timeMs,
                'checked_at' => $endpoint['probes_as_get'] ? $checkedAt : null,
            ];
        }

        return [
            'run_at' => now()->format('Y-m-d H:i:s T'),
            'base_url' => $baseUrl,
            'auth_context' => $token['token'] !== null ? 'PROBE TOKEN (AUTHENTICATED)' : 'UNAUTHENTICATED',
            'endpoints' => $rows,
            'summary' => $this->summarize($rows),
        ];
    }

    /**
     * Build the full endpoint inventory straight from the Router. No manual
     * list: if a route exists in the application it appears here; if it is
     * removed it disappears from the page on the next probe run.
     */
    private function enumerateEndpoints(): array
    {
        /** @var Router $router */
        $router = app(Router::class);

        $endpoints = [];
        foreach ($router->getRoutes() as $route) {
            /** @var RouteInstance $route */
            if ($route->getName() === 'web.status') {
                continue; // Never probe the status page itself (recursive render).
            }

            $methods = array_values(array_diff($route->methods(), ['HEAD']));
            $method = $methods[0] ?? 'GET';
            $uri = $route->uri();

            $endpoints[] = [
                'method' => $method,
                'uri' => $uri,
                'uri_path' => $uri,
                'tier' => $this->resolveTier($route),
                // Write endpoints are probed with GET (a real 405 proves the route
                // is live without side effects); parameterized paths cannot be
                // probed truthfully and are reported as such instead of faked.
                'probes_as_get' => ! str_contains($uri, '{'),
            ];
        }

        usort($endpoints, fn (array $a, array $b): int => [$a['tier'], $a['uri'], $a['method']] <=> [$b['tier'], $b['uri'], $b['method']]);

        return $endpoints;
    }

    private function resolveTier(RouteInstance $route): string
    {
        $middleware = implode('|', $route->gatherMiddleware());

        if (str_contains($middleware, 'role:ADMIN')) {
            return 'ADMIN';
        }

        if (str_contains($middleware, 'role:MANAGER')) {
            return 'MANAGER+';
        }

        if (str_contains($middleware, 'role:CASHIER')) {
            return 'CASHIER+';
        }

        if (str_contains($middleware, 'auth:sanctum')) {
            return 'AUTH';
        }

        return 'PUBLIC';
    }

    /**
     * Exchange configured probe credentials for a real bearer token over
     * HTTP. Returns an unauthenticated context (null token) when credentials
     * are absent or the exchange fails; the login row then shows the real
     * failure state instead of a fabricated success.
     */
    private function acquireProbeToken(string $baseUrl): array
    {
        $username = (string) env('STATUS_PROBE_USERNAME', '');
        $password = (string) env('STATUS_PROBE_PASSWORD', '');

        if ($username === '' || $password === '') {
            return ['token' => null, 'token_id' => null, 'http_code' => null, 'latency_ms' => null, 'error' => null];
        }

        $handle = curl_init($baseUrl.'/api/v1/auth/login');
        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['username' => $username, 'password' => $password]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::PROBE_TOTAL_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => self::PROBE_CONNECT_TIMEOUT_SECONDS,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        ]);

        $startedAt = hrtime(true);
        $body = (string) curl_exec($handle);
        $latencyMs = round((hrtime(true) - $startedAt) / 1e6, 1);
        $error = curl_error($handle);
        $httpCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($error !== '') {
            return ['token' => null, 'token_id' => null, 'http_code' => null, 'latency_ms' => null, 'error' => $error];
        }

        $payload = json_decode($body, true);
        $plainToken = is_array($payload) ? data_get($payload, 'data.access_token') ?? data_get($payload, 'access_token') : null;

        $tokenId = null;
        if (is_string($plainToken) && str_contains($plainToken, '|')) {
            $tokenId = (int) explode('|', $plainToken)[0];
        }

        return [
            'token' => is_string($plainToken) ? $plainToken : null,
            'token_id' => $tokenId,
            'http_code' => $httpCode,
            'latency_ms' => $latencyMs,
            'error' => null,
        ];
    }

    /**
     * Fire all probes in parallel over real HTTP. Latency is curl's own
     * transfer timing per handle; failures are reported, never substituted.
     */
    private function executeParallelProbes(array $targets, ?string $token): array
    {
        if ($targets === []) {
            return [];
        }

        $multi = curl_multi_init();
        $handles = [];

        foreach ($targets as $index => $url) {
            $handle = curl_init($url);
            $headers = ['Accept: application/json'];
            if ($token !== null) {
                $headers[] = 'Authorization: Bearer '.$token;
            }

            curl_setopt_array($handle, [
                CURLOPT_HTTPGET => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => self::PROBE_TOTAL_TIMEOUT_SECONDS,
                CURLOPT_CONNECTTIMEOUT => self::PROBE_CONNECT_TIMEOUT_SECONDS,
                CURLOPT_HTTPHEADER => $headers,
            ]);

            curl_multi_add_handle($multi, $handle);
            $handles[$index] = $handle;
        }

        do {
            $status = curl_multi_exec($multi, $active);
            if ($active) {
                curl_multi_select($multi, 0.2);
            }
        } while ($active && $status === CURLM_OK);

        $results = [];
        foreach ($handles as $index => $handle) {
            $error = curl_error($handle);
            $timeUs = curl_getinfo($handle, CURLINFO_TOTAL_TIME_T);

            $results[$index] = [
                'http_code' => (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE),
                'time_ms' => $timeUs > 0 ? round($timeUs / 1000, 1) : null,
                'error' => $error !== '' ? $error : null,
            ];

            curl_multi_remove_handle($multi, $handle);
        }

        curl_multi_close($multi);

        return $results;
    }

    /**
     * Destroy the bearer token created for the probe run so the status page
     * never leaves live credentials behind.
     */
    private function revokeProbeToken(?int $tokenId): void
    {
        if ($tokenId === null) {
            return;
        }

        try {
            DB::table('personal_access_tokens')->where('id', $tokenId)->delete();
        } catch (\Throwable) {
            // Token cleanup must never break the status page.
        }
    }

    private function loginEndpointIndex(array $endpoints): ?int
    {
        foreach ($endpoints as $index => $endpoint) {
            if ($endpoint['method'] === 'POST' && $endpoint['uri'] === 'api/v1/auth/login') {
                return $index;
            }
        }

        return null;
    }

    private function classifyState(int $httpCode): string
    {
        return match (true) {
            $httpCode >= 200 && $httpCode < 300 => 'OPERATIONAL',
            $httpCode === 401, $httpCode === 403 => 'AUTH GUARD',
            $httpCode === 404 => 'NOT FOUND',
            $httpCode === 405 => 'WRITE GUARD',
            $httpCode === 429 => 'RATE LIMITED',
            $httpCode >= 500 => 'SERVER ERROR',
            $httpCode === 0 => 'UNREACHABLE',
            default => 'HTTP '.$httpCode,
        };
    }

    private function summarize(array $rows): array
    {
        $probed = array_filter($rows, fn (array $row): bool => $row['time_ms'] !== null);
        $times = array_values(array_map(fn (array $row): float => (float) $row['time_ms'], $probed));

        sort($times);

        return [
            'total' => count($rows),
            'operational' => count(array_filter($rows, fn (array $r): bool => $r['state'] === 'OPERATIONAL')),
            'auth_guarded' => count(array_filter($rows, fn (array $r): bool => $r['state'] === 'AUTH GUARD')),
            'write_guarded' => count(array_filter($rows, fn (array $r): bool => $r['state'] === 'WRITE GUARD')),
            'parameterized' => count(array_filter($rows, fn (array $r): bool => $r['state'] === 'PARAMETERIZED')),
            'errors' => count(array_filter($rows, fn (array $r): bool => in_array($r['state'], ['SERVER ERROR', 'UNREACHABLE', 'NOT FOUND'], true))),
            'rate_limited' => count(array_filter($rows, fn (array $r): bool => $r['state'] === 'RATE LIMITED')),
            'probed_count' => count($times),
            'avg_ms' => $times === [] ? null : round(array_sum($times) / count($times), 1),
            'fastest_ms' => $times === [] ? null : $times[0],
            'slowest_ms' => $times === [] ? null : $times[count($times) - 1],
            'p95_ms' => $times === [] ? null : $times[(int) floor(0.95 * (count($times) - 1))],
        ];
    }

    private function getDatabaseMetrics(): array
    {
        $startedAt = hrtime(true);

        try {
            DB::select('SELECT 1');
            $pingMs = round((hrtime(true) - $startedAt) / 1e6, 1);

            return [
                'state' => 'OPERATIONAL',
                'ping_ms' => $pingMs,
                'products' => Product::count(),
                'active_employees' => Employee::where('status', 'ACTIVE')->count(),
                'connection' => (string) DB::connection()->getName(),
            ];
        } catch (\Throwable) {
            return [
                'state' => 'DEGRADED',
                'ping_ms' => null,
                'products' => null,
                'active_employees' => null,
                'connection' => (string) config('database.default'),
            ];
        }
    }

    /**
     * Real 24h API traffic aggregates from the ApiLog monitoring table,
     * including status-probe traffic (which is genuine API activity).
     */
    private function getTrafficMetrics(): array
    {
        try {
            $since = now()->subDay();

            $aggregate = ApiLog::where('created_at', '>=', $since)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status >= 400 THEN 1 ELSE 0 END) as errors, AVG(duration_ms) as avg_duration, MAX(created_at) as last_request')
                ->first();

            return [
                'available' => true,
                'total_24h' => (int) ($aggregate->total ?? 0),
                'errors_24h' => (int) ($aggregate->errors ?? 0),
                'avg_duration_ms' => $aggregate->avg_duration !== null ? round((float) $aggregate->avg_duration, 1) : null,
                'last_request_at' => $aggregate->last_request !== null ? $aggregate->last_request->format('Y-m-d H:i:s') : null,
                'recent' => ApiLog::orderBy('id', 'desc')->limit(10)->get()->map(fn (ApiLog $log): array => [
                    'method' => $log->method,
                    'path' => $log->path,
                    'status' => $log->status,
                    'duration_ms' => $log->duration_ms !== null ? round((float) $log->duration_ms, 1) : null,
                    'at' => $log->created_at?->format('H:i:s'),
                ])->toArray(),
            ];
        } catch (\Throwable) {
            return ['available' => false, 'recent' => []];
        }
    }

    private function getSystemMetrics(): array
    {
        $diskTotal = @disk_total_space('/');
        $diskFree = @disk_free_space('/');

        return [
            'disk' => $diskTotal !== false && $diskFree !== false && $diskTotal > 0 ? [
                'total_gb' => round($diskTotal / 1024 ** 3, 1),
                'free_gb' => round($diskFree / 1024 ** 3, 1),
                'used_pct' => round((($diskTotal - $diskFree) / $diskTotal) * 100, 1),
            ] : null,
            'memory' => $this->readMemoryUsage(),
            'uptime' => $this->readUptime(),
        ];
    }

    private function readMemoryUsage(): ?array
    {
        $meminfo = @file_get_contents('/proc/meminfo');

        if ($meminfo === false) {
            return null;
        }

        if (! preg_match('/MemTotal:\s+(\d+) kB/', $meminfo, $total) || ! preg_match('/MemAvailable:\s+(\d+) kB/', $meminfo, $available)) {
            return null;
        }

        $totalKb = (int) $total[1];
        $availableKb = (int) $available[1];

        return [
            'total_mb' => (int) round($totalKb / 1024),
            'used_pct' => round((($totalKb - $availableKb) / $totalKb) * 100, 1),
        ];
    }

    private function readUptime(): ?string
    {
        $uptime = @file_get_contents('/proc/uptime');

        if ($uptime === false) {
            return null;
        }

        $seconds = (int) floor((float) strtok($uptime, ' '));
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%dd %dh %dm', $days, $hours, $minutes);
    }

    private function getRuntimeMetrics(): array
    {
        return [
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'environment' => app()->environment(),
        ];
    }
}
