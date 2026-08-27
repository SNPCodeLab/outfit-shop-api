<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>System Status | OutfitShop Backend API</title>
<meta name="robots" content="noindex">
<style>
    :root {
        --bg: #0f0d09;
        --panel: #1a160d;
        --panel-edge: #2b2518;
        --row-alt: #1e1a12;
        --text: #e8e2d4;
        --text-dim: #8f8875;
        --text-faint: #5f5a4b;
        --accent: #e8b64c;
        --green: #4cc38a;
        --amber: #e8b64c;
        --red: #e5534b;
        --blue: #6ca4e8;
        --gray: #8f8875;
        --radius: 3px;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: var(--bg);
        color: var(--text);
        font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
        font-size: 12px;
        line-height: 1.4;
        padding-bottom: 64px;
    }

    /* ── Header ────────────────────────────────────────── */
    .header {
        background: var(--panel);
        border-bottom: 1px solid var(--panel-edge);
        padding: 0 24px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 32px;
        margin-bottom: 32px;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .header-logo {
        position: absolute;
        left: 24px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text);
    }

    .nav-item {
        color: var(--text-dim);
        text-decoration: none;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 6px 12px;
        border-radius: var(--radius);
        transition: color 0.2s;
    }

    .nav-item:hover { color: var(--text); }
    .nav-item.active {
        background: #e8e2d4;
        color: #000;
    }

    .update-badge {
        position: absolute;
        right: 24px;
        color: var(--green);
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.05em;
    }

    /* ── Wrap ─────────────────────────────────────────── */
    .wrap { max-width: 1360px; margin: 0 auto; padding: 0 24px; }

    /* ── Grid ─────────────────────────────────────────── */
    .cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 32px;
    }

    .card {
        background: var(--panel);
        border: 1px solid var(--panel-edge);
        border-radius: 8px;
        padding: 16px 20px;
        min-height: 160px;
        display: flex;
        flex-direction: column;
    }

    .card-label {
        font-size: 9px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--accent);
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
    }

    .card-label span.meta { color: var(--text-dim); letter-spacing: normal; text-transform: none; font-size: 10px; }

    .card-body { flex: 1; position: relative; }

    .card-value { font-size: 32px; font-weight: 700; line-height: 1.1; margin-bottom: 4px; }
    .card-value small { font-size: 14px; color: var(--text-dim); margin-left: 4px; font-weight: 400; }

    .card-sub { font-size: 11px; color: var(--text-dim); margin-top: 2px; }
    .card-sub.v-red { color: var(--red); }
    .card-sub.v-green { color: var(--green); }

    /* ── Progress Bars ────────────────────────────────── */
    .progress-wrap { margin-top: 16px; }
    .progress-track { background: #2b2518; height: 6px; border-radius: 2px; overflow: hidden; margin-top: 8px; }
    .progress-fill { height: 100%; border-radius: 2px; transition: width 0.4s ease; }
    .fill-amber { background: linear-gradient(90deg, #e8b64c 0%, #ffdf9e 100%); }
    .fill-blue { background: linear-gradient(90deg, #6ca4e8 0%, #a8ccf5 100%); }

    /* ── Large Health Score ───────────────────────────── */
    .health-score { display: flex; align-items: flex-start; gap: 20px; }
    .score-num { font-size: 56px; font-weight: 700; color: var(--text); line-height: 0.8; }
    .score-meta { flex: 1; }
    .score-meta .status { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
    .score-meta .detail { font-size: 11px; color: var(--text-dim); line-height: 1.4; }

    /* ── Inventory / Processes Table ───────────────────── */
    section h2 {
        font-size: 10px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--text-dim);
        margin-bottom: 12px;
        padding-left: 4px;
    }

    .table-wrap {
        background: var(--panel);
        border: 1px solid var(--panel-edge);
        border-radius: 8px;
        overflow: hidden;
    }

    table { width: 100%; border-collapse: collapse; }

    th {
        text-align: left;
        font-size: 9px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--text-faint);
        padding: 12px 16px;
        border-bottom: 1px solid var(--panel-edge);
        white-space: nowrap;
    }

    td {
        padding: 8px 16px;
        border-bottom: 1px solid #241e13;
        white-space: nowrap;
        font-size: 11.5px;
    }

    tr:nth-child(even) td { background: var(--row-alt); }
    tr:last-child td { border-bottom: none; }

    .t-name { color: var(--text); font-weight: 600; }
    .t-dim { color: var(--text-dim); }
    .t-faint { color: var(--text-faint); }

    .status-bar {
        display: inline-block;
        width: 3px;
        height: 14px;
        margin-right: 12px;
        vertical-align: middle;
        border-radius: 1px;
    }
    .bg-green { background: var(--green); }
    .bg-amber { background: var(--amber); }
    .bg-red { background: var(--red); }
    .bg-blue { background: var(--blue); }
    .bg-dim { background: var(--text-faint); }

    .micro-bar-bg { background: #2b2518; width: 40px; height: 4px; border-radius: 1px; display: inline-block; vertical-align: middle; margin-right: 8px; overflow: hidden; }
    .micro-bar-fill { height: 100%; background: var(--amber); }

    .footnote {
        margin-top: 16px;
        font-size: 10px;
        color: var(--text-faint);
        padding-left: 4px;
    }

    @media (max-width: 1100px) {
        .cards { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .cards { grid-template-columns: 1fr; }
        .header { display: none; }
    }
</style>
</head>
<body>

<header class="header">
    <div class="header-logo">OutfitShop MIS</div>
    <a href="#" class="nav-item">Clean</a>
    <a href="#" class="nav-item">Apps</a>
    <a href="#" class="nav-item">Optimize</a>
    <a href="#" class="nav-item">Analyze</a>
    <a href="#" class="nav-item active">Status</a>
    <div class="update-badge">UPDATE {{ config('api.version', '1.2.0') }}</div>
</header>

<div class="wrap">

    <div class="cards">
        <!-- CARD 1: HEALTH -->
        <div class="card">
            <div class="card-label">
                HEALTH
                <span class="meta">PHP {{ $runtime['php'] }}</span>
            </div>
            <div class="card-body">
                <div class="health-score">
                    <div class="score-num">{{ $health_score }}</div>
                    <div class="score-meta">
                        <div class="status">{{ $health_score >= 90 ? 'EXCELLENT' : ($health_score >= 70 ? 'GOOD' : 'DEGRADED') }}</div>
                        <div class="detail">
                            {{ $probe['summary']['operational'] }} operational endpoints.<br>
                            {{ $probe['summary']['errors'] }} failure detected.<br>
                            Last run {{ $probe['run_at'] }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: DATABASE -->
        <div class="card">
            <div class="card-label">
                DATABASE
                <span class="meta">{{ $database['connection'] }}</span>
            </div>
            <div class="card-body">
                @if ($database['state'] === 'OPERATIONAL')
                    <div class="card-value">{{ $database['ping_ms'] }}<small>ms</small></div>
                    <div class="card-sub v-green">STABLE CONNECTION</div>
                    <div class="card-sub">{{ number_format($database['products']) }} products in catalog</div>
                    <div class="card-sub">{{ $database['active_employees'] }} employees on duty</div>
                @else
                    <div class="card-value v-red">OFFLINE</div>
                    <div class="card-sub v-red">CONNECTION FAILED</div>
                @endif
            </div>
        </div>

        <!-- CARD 3: TRAFFIC -->
        <div class="card">
            <div class="card-label">
                TRAFFIC
                <span class="meta">Last 24h</span>
            </div>
            <div class="card-body">
                @if ($traffic['available'])
                    <div class="card-value">{{ number_format($traffic['total_24h']) }}<small>reqs</small></div>
                    <div class="card-sub">{{ number_format($traffic['errors_24h']) }} errors recorded</div>
                    @if ($traffic['avg_duration_ms'] !== null)
                        <div class="card-sub">avg processing {{ $traffic['avg_duration_ms'] }} ms</div>
                    @endif
                @else
                    <div class="card-value t-faint">0</div>
                    <div class="card-sub">Logs table not reachable</div>
                @endif
            </div>
        </div>

        <!-- CARD 4: MEMORY -->
        <div class="card">
            <div class="card-label">
                MEMORY
                <span class="meta">Pressure {{ $system['memory']['used_pct'] ?? '0' }}%</span>
            </div>
            <div class="card-body">
                @if ($system['memory'] !== null)
                    <div class="card-value">{{ $system['memory']['used_pct'] }}<small>%</small></div>
                    <div class="progress-wrap">
                        <div class="progress-track">
                            <div class="progress-fill fill-amber" style="width: {{ $system['memory']['used_pct'] }}%"></div>
                        </div>
                        <div class="card-sub">{{ number_format($system['memory']['total_mb']) }} MB TOTAL CAPACITY</div>
                    </div>
                @else
                    <div class="card-value t-faint">N/A</div>
                    <div class="card-sub">Platform metrics restricted</div>
                @endif
            </div>
        </div>

        <!-- CARD 5: UPTIME -->
        <div class="card">
            <div class="card-label">UPTIME</div>
            <div class="card-body">
                @if ($system['uptime'] !== null)
                    <div class="card-value" style="font-size:24px;">{{ $system['uptime'] }}</div>
                    <div class="card-sub">SINCE SYSTEM BOOT</div>
                @else
                    <div class="card-value t-faint">N/A</div>
                    <div class="card-sub">Uptime stats not readable</div>
                @endif
            </div>
        </div>

        <!-- CARD 6: DISK -->
        <div class="card">
            <div class="card-label">
                DISK
                <span class="meta">{{ $system['disk']['total_gb'] ?? '0' }} GB Capacity</span>
            </div>
            <div class="card-body">
                @if ($system['disk'] !== null)
                    <div class="card-value">{{ $system['disk']['free_gb'] }}<small>GB Free</small></div>
                    <div class="progress-wrap">
                        <div class="progress-track">
                            <div class="progress-fill fill-blue" style="width: {{ $system['disk']['used_pct'] }}%"></div>
                        </div>
                        <div class="card-sub">{{ $system['disk']['used_pct'] }}% DISK STORAGE UTILIZED</div>
                    </div>
                @else
                    <div class="card-value t-faint">N/A</div>
                    <div class="card-sub">Storage metrics restricted</div>
                @endif
            </div>
        </div>

        <!-- CARD 7: PERFORMANCE -->
        <div class="card">
            <div class="card-label">LATENCY</div>
            <div class="card-body">
                @if ($probe['summary']['avg_ms'] !== null)
                    <div class="card-value">{{ $probe['summary']['avg_ms'] }}<small>ms</small></div>
                    <div class="card-sub">p95 response {{ $probe['summary']['p95_ms'] }} ms</div>
                    <div class="card-sub">FASTEST {{ $probe['summary']['fastest_ms'] }} ms</div>
                @else
                    <div class="card-value t-faint">N/A</div>
                    <div class="card-sub">No probe metrics this run</div>
                @endif
            </div>
        </div>

        <!-- CARD 8: RUNTIME -->
        <div class="card">
            <div class="card-label">RUNTIME</div>
            <div class="card-body">
                <div class="card-value" style="font-size:24px;">Laravel {{ $runtime['laravel'] }}</div>
                <div class="card-sub">ENVIRONMENT: {{ strtoupper($runtime['environment']) }}</div>
                <div class="card-sub">API VERSION: {{ config('api.version', '1.2.0') }}</div>
            </div>
        </div>
    </div>

    <section>
        <h2>ENDPOINT INVENTORY ({{ count($probe['endpoints']) }})</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>NAME (METHOD)</th>
                        <th>URI</th>
                        <th>TIER</th>
                        <th>LIVE STATE</th>
                        <th>HTTP</th>
                        <th>LATENCY</th>
                        <th>CHECKED</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($probe['endpoints'] as $endpoint)
                        @php
                            $stateBg = match (true) {
                                $endpoint['state'] === 'OPERATIONAL' => 'bg-green',
                                in_array($endpoint['state'], ['AUTH GUARD', 'RATE LIMITED'], true) => 'bg-amber',
                                $endpoint['state'] === 'WRITE GUARD' => 'bg-blue',
                                in_array($endpoint['state'], ['SERVER ERROR', 'UNREACHABLE', 'NOT FOUND'], true) => 'bg-red',
                                default => 'bg-dim',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="status-bar {{ $stateBg }}"></span>
                                <span class="t-name">{{ strtoupper($endpoint['method']) }}</span>
                            </td>
                            <td class="t-name">/{{ $endpoint['uri'] }}</td>
                            <td class="t-faint">{{ $endpoint['tier'] }}</td>
                            <td class="t-name" style="letter-spacing:0.05em; font-size:10px;">{{ $endpoint['state'] }}</td>
                            <td class="t-name">{{ $endpoint['http_code'] ?? '-' }}</td>
                            <td>
                                @if ($endpoint['time_ms'] !== null)
                                    <div class="micro-bar-bg"><div class="micro-bar-fill" style="width: {{ min(100, $endpoint['time_ms'] / 10) }}%"></div></div>
                                    <span class="t-dim">{{ $endpoint['time_ms'] }} ms</span>
                                @else
                                    <span class="t-faint">-</span>
                                @endif
                            </td>
                            <td class="t-faint">{{ $endpoint['checked_at'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="footnote">
            States: OPERATIONAL (2xx), AUTH GUARD (401/403), WRITE GUARD (405 on GET), PARAMETERIZED (Static Path).
            System render {{ $generated_at }}.
        </p>
    </section>

</div>
</body>
</html>
