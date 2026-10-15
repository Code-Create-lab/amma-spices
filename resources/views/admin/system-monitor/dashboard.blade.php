<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sys Monitor · Pro</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    :root {
        --bg: #0a0b14;
        --bg-2: #0f1120;
        --panel: rgba(20, 22, 38, 0.65);
        --panel-2: rgba(28, 30, 50, 0.55);
        --border: rgba(255, 255, 255, 0.08);
        --border-2: rgba(255, 255, 255, 0.14);
        --text: #e7e9f5;
        --text-2: #9aa0c7;
        --text-3: #6b7196;
        --accent: #7c5cff;
        --accent-2: #4cc9f0;
        --success: #2dd4bf;
        --warn: #fbbf24;
        --danger: #f87171;
        --pink: #f472b6;
        --shadow: 0 18px 60px rgba(0, 0, 0, 0.45);
        --radius: 18px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body {
        font-family: 'Inter', -apple-system, sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }
    body::before {
        content: '';
        position: fixed; inset: 0;
        background:
            radial-gradient(900px 600px at 12% 8%, rgba(124, 92, 255, 0.30), transparent 60%),
            radial-gradient(800px 500px at 92% 4%, rgba(76, 201, 240, 0.20), transparent 60%),
            radial-gradient(700px 500px at 50% 100%, rgba(244, 114, 182, 0.16), transparent 60%);
        pointer-events: none;
        z-index: 0;
    }
    .layout { position: relative; z-index: 1; max-width: 1480px; margin: 0 auto; padding: 28px; }
    .topbar {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 24px;
        padding: 18px 22px;
        background: var(--panel);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }
    .brand { display: flex; align-items: center; gap: 14px; }
    .brand .logo {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #7c5cff 0%, #4cc9f0 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        box-shadow: 0 6px 24px rgba(124, 92, 255, 0.5);
    }
    .brand .title { font-size: 18px; font-weight: 700; letter-spacing: -0.02em; }
    .brand .subtitle { font-size: 12px; color: var(--text-3); font-weight: 500; }
    .status-pill {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 14px;
        background: rgba(45, 212, 191, 0.12);
        border: 1px solid rgba(45, 212, 191, 0.35);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: var(--success);
    }
    .status-pill .dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 0 rgba(45, 212, 191, 0.6);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%   { box-shadow: 0 0 0 0 rgba(45, 212, 191, 0.6); }
        70%  { box-shadow: 0 0 0 10px rgba(45, 212, 191, 0); }
        100% { box-shadow: 0 0 0 0 rgba(45, 212, 191, 0); }
    }
    .topbar .meta { display: flex; gap: 22px; font-size: 12px; color: var(--text-2); }
    .topbar .meta b { color: var(--text); font-weight: 600; }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }
    .metric {
        position: relative;
        padding: 22px;
        background: var(--panel);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
        transition: transform .25s ease, border-color .25s ease;
    }
    .metric:hover { transform: translateY(-3px); border-color: var(--border-2); }
    .metric .label {
        display: flex; align-items: center; gap: 8px;
        font-size: 11px; font-weight: 700;
        color: var(--text-3);
        letter-spacing: 0.14em; text-transform: uppercase;
        margin-bottom: 12px;
    }
    .metric .icon {
        width: 28px; height: 28px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
    }
    .metric .value {
        font-family: 'JetBrains Mono', monospace;
        font-size: 36px; font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1;
    }
    .metric .unit { font-size: 14px; color: var(--text-3); font-weight: 500; margin-left: 4px; }
    .metric .sub {
        margin-top: 10px;
        font-size: 12px;
        color: var(--text-2);
        font-weight: 500;
        display: flex; justify-content: space-between;
    }
    .metric .bar {
        height: 6px; border-radius: 999px;
        background: rgba(255, 255, 255, 0.06);
        overflow: hidden;
        margin-top: 14px;
    }
    .metric .bar > span {
        display: block; height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--accent), var(--accent-2));
        transition: width .6s ease;
        width: 0%;
    }
    .metric.cpu  .icon { background: rgba(124, 92, 255, 0.18); color: var(--accent); }
    .metric.ram  .icon { background: rgba(76, 201, 240, 0.18); color: var(--accent-2); }
    .metric.disk .icon { background: rgba(45, 212, 191, 0.18); color: var(--success); }
    .metric.net  .icon { background: rgba(251, 191, 36, 0.18); color: var(--warn); }
    .metric.users .icon { background: rgba(244, 114, 182, 0.18); color: var(--pink); }
    .metric.uptime .icon { background: rgba(248, 113, 113, 0.18); color: var(--danger); }

    .charts-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 18px;
        margin-bottom: 22px;
    }
    @media (max-width: 980px) { .charts-grid { grid-template-columns: 1fr; } }
    .panel {
        background: var(--panel);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 22px;
    }
    .panel-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 16px;
    }
    .panel-title { font-size: 14px; font-weight: 700; letter-spacing: -0.01em; }
    .panel-hint { font-size: 11px; color: var(--text-3); }
    .chart-wrap { position: relative; height: 240px; }

    .net-row { display: flex; gap: 14px; }
    .net-card {
        flex: 1;
        padding: 16px;
        background: var(--panel-2);
        border: 1px solid var(--border);
        border-radius: 12px;
    }
    .net-card .l { font-size: 11px; color: var(--text-3); letter-spacing: .12em; text-transform: uppercase; font-weight: 700; }
    .net-card .v { font-family: 'JetBrains Mono', monospace; font-size: 22px; font-weight: 700; margin-top: 6px; }
    .net-card.rx .v { color: var(--accent-2); }
    .net-card.tx .v { color: var(--pink); }
    .net-card .t { font-size: 11px; color: var(--text-3); margin-top: 6px; }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }
    .action-card {
        background: var(--panel);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 22px;
    }
    .action-card h3 {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-2);
        margin-bottom: 6px;
        display: flex; align-items: center; gap: 8px;
    }
    .action-card .hint { font-size: 12px; color: var(--text-3); margin-bottom: 14px; }
    .btn-row { display: flex; flex-wrap: wrap; gap: 8px; }
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 14px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border);
        color: var(--text);
        font-size: 12px;
        font-weight: 600;
        border-radius: 10px;
        text-decoration: none;
        cursor: pointer;
        transition: all .2s;
        font-family: inherit;
    }
    .btn:hover { background: rgba(255, 255, 255, 0.10); border-color: var(--border-2); transform: translateY(-1px); }
    .btn.primary { background: linear-gradient(135deg, #7c5cff, #4cc9f0); border-color: transparent; }
    .btn.primary:hover { filter: brightness(1.1); }
    .btn.success { background: rgba(45, 212, 191, 0.16); border-color: rgba(45, 212, 191, 0.35); color: var(--success); }
    .btn.warn    { background: rgba(251, 191, 36, 0.14); border-color: rgba(251, 191, 36, 0.32); color: var(--warn); }
    .btn.danger  { background: rgba(248, 113, 113, 0.14); border-color: rgba(248, 113, 113, 0.32); color: var(--danger); }
    .btn.block { width: 100%; justify-content: center; }

    .custom-form input[type="text"] {
        width: 100%;
        padding: 12px 14px;
        background: rgba(0, 0, 0, 0.28);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text);
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
        margin-bottom: 10px;
    }
    .custom-form input[type="text"]:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(124, 92, 255, 0.18);
    }

    .info-stripe {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 22px;
        box-shadow: var(--shadow);
    }
    .info-stripe .item .l { font-size: 11px; color: var(--text-3); letter-spacing: .12em; text-transform: uppercase; font-weight: 700; }
    .info-stripe .item .v { font-size: 14px; font-weight: 600; margin-top: 4px; font-family: 'JetBrains Mono', monospace; word-break: break-all; }

    .skeleton {
        background: linear-gradient(90deg, rgba(255,255,255,0.04), rgba(255,255,255,0.1), rgba(255,255,255,0.04));
        background-size: 200% 100%;
        animation: shimmer 1.4s infinite;
        border-radius: 6px;
    }
    @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    .scrollx { overflow-x: auto; }
    .iface-table { width: 100%; border-collapse: collapse; font-family: 'JetBrains Mono', monospace; font-size: 12px; margin-top: 10px; }
    .iface-table th { text-align: left; color: var(--text-3); font-weight: 600; padding: 8px 10px; border-bottom: 1px solid var(--border); }
    .iface-table td { padding: 8px 10px; border-bottom: 1px solid var(--border); color: var(--text-2); }
    .iface-table td:first-child { color: var(--text); font-weight: 600; }
</style>
</head>
<body>
<div class="layout">

    <header class="topbar">
        <div class="brand">
            <div class="logo">⚡</div>
            <div>
                <div class="title">System Monitor · Pro Max</div>
                <div class="subtitle">Live infrastructure telemetry &amp; Artisan console</div>
            </div>
        </div>
        <div class="meta">
            <div><b>OS:</b> <span id="m-os">—</span></div>
            <div><b>Host:</b> <span id="m-host">—</span></div>
            <div><b>Laravel:</b> <span id="m-laravel">—</span></div>
            <div><b>PHP:</b> <span id="m-php">—</span></div>
        </div>
        <div class="status-pill"><span class="dot"></span><span id="m-live">LIVE</span></div>
    </header>

    <section class="metrics-grid">
        <div class="metric cpu">
            <div class="label"><span class="icon">⚙️</span> CPU Usage</div>
            <div><span class="value" id="m-cpu">--</span><span class="unit">%</span></div>
            <div class="sub"><span id="m-cpu-cores">— cores</span><span id="m-cpu-load">load —</span></div>
            <div class="bar"><span id="m-cpu-bar"></span></div>
        </div>
        <div class="metric ram">
            <div class="label"><span class="icon">🧠</span> Memory</div>
            <div><span class="value" id="m-ram">--</span><span class="unit">%</span></div>
            <div class="sub"><span id="m-ram-used">— used</span><span id="m-ram-total">— total</span></div>
            <div class="bar"><span id="m-ram-bar"></span></div>
        </div>
        <div class="metric disk">
            <div class="label"><span class="icon">💾</span> Disk</div>
            <div><span class="value" id="m-disk">--</span><span class="unit">%</span></div>
            <div class="sub"><span id="m-disk-used">— used</span><span id="m-disk-free">— free</span></div>
            <div class="bar"><span id="m-disk-bar"></span></div>
        </div>
        <div class="metric net">
            <div class="label"><span class="icon">📡</span> Bandwidth</div>
            <div><span class="value" id="m-net">--</span><span class="unit">/s</span></div>
            <div class="sub"><span id="m-net-rx">↓ —</span><span id="m-net-tx">↑ —</span></div>
            <div class="bar"><span id="m-net-bar"></span></div>
        </div>
        <div class="metric users">
            <div class="label"><span class="icon">👥</span> Active Users</div>
            <div><span class="value" id="m-users">--</span></div>
            <div class="sub"><span id="m-users-logged">— logged-in</span><span id="m-users-total">— total sess</span></div>
        </div>
        <div class="metric uptime">
            <div class="label"><span class="icon">⏱️</span> Uptime</div>
            <div><span class="value" id="m-uptime">--</span></div>
            <div class="sub"><span id="m-queue">queue —</span><span id="m-failed">failed —</span></div>
        </div>
    </section>

    <section class="charts-grid">
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📈 CPU &amp; Memory · 60s window</div>
                <div class="panel-hint">3s refresh</div>
            </div>
            <div class="chart-wrap"><canvas id="chart-cpu"></canvas></div>
        </div>
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">📡 Network Throughput</div>
                <div class="panel-hint" id="net-driver-hint">RX / TX bytes per second</div>
            </div>
            <div class="net-row" style="margin-bottom: 14px;">
                <div class="net-card rx"><div class="l">↓ Download</div><div class="v" id="net-rx-rate">— /s</div><div class="t" id="net-rx-total">total —</div></div>
                <div class="net-card tx"><div class="l">↑ Upload</div><div class="v" id="net-tx-rate">— /s</div><div class="t" id="net-tx-total">total —</div></div>
            </div>
            <div class="chart-wrap" style="height: 130px;"><canvas id="chart-net"></canvas></div>
        </div>
    </section>

    <section class="info-stripe" style="margin-bottom: 22px;">
        <div class="item"><div class="l">Environment</div><div class="v" id="i-env">—</div></div>
        <div class="item"><div class="l">Debug</div><div class="v" id="i-debug">—</div></div>
        <div class="item"><div class="l">Timezone</div><div class="v" id="i-tz">—</div></div>
        <div class="item"><div class="l">PHP Mem Limit</div><div class="v" id="i-mlimit">—</div></div>
        <div class="item"><div class="l">Upload Max</div><div class="v" id="i-upmax">—</div></div>
        <div class="item"><div class="l">OPcache</div><div class="v" id="i-opcache">—</div></div>
        <div class="item"><div class="l">Session Driver</div><div class="v" id="i-sess">—</div></div>
        <div class="item"><div class="l">Last Update</div><div class="v" id="i-ts">—</div></div>
    </section>

    <section class="actions-grid">
        <div class="action-card">
            <h3>📋 Application Logs</h3>
            <div class="hint">Daily log viewer with date / search filters</div>
            <div class="btn-row">
                <a href="?password={{ $password }}&action=logs" class="btn primary">📖 Open Logs</a>
                <a href="?password={{ $password }}&action=logs&search=error" class="btn danger">🔴 Errors Only</a>
            </div>
        </div>

        <div class="action-card">
            <h3>🗑️ Clear Caches</h3>
            <div class="hint">Wipe runtime caches &amp; bootstrap files</div>
            <div class="btn-row">
                <a href="?password={{ $password }}&action=run&cmd=cache:clear" class="btn">Cache</a>
                <a href="?password={{ $password }}&action=run&cmd=config:clear" class="btn">Config</a>
                <a href="?password={{ $password }}&action=run&cmd=route:clear" class="btn">Route</a>
                <a href="?password={{ $password }}&action=run&cmd=view:clear" class="btn">View</a>
                <a href="?password={{ $password }}&action=run&cmd=optimize:clear" class="btn warn">Clear All</a>
                <a href="?password={{ $password }}&action=clear_sessions" class="btn danger" onclick="return confirm('Delete all sessions? Users will be logged out.');">🔐 Sessions</a>
            </div>
        </div>

        <div class="action-card">
            <h3>⚡ Optimization</h3>
            <div class="hint">Cache for performance</div>
            <div class="btn-row">
                <a href="?password={{ $password }}&action=run&cmd=config:cache" class="btn success">Config</a>
                <a href="?password={{ $password }}&action=run&cmd=route:cache" class="btn success">Route</a>
                <a href="?password={{ $password }}&action=run&cmd=view:cache" class="btn success">View</a>
                <a href="?password={{ $password }}&action=run&cmd=optimize" class="btn success">Optimize All</a>
            </div>
        </div>

        <div class="action-card">
            <h3>💾 Database</h3>
            <div class="hint">Migrations &amp; seeders</div>
            <div class="btn-row">
                <a href="?password={{ $password }}&action=run&cmd=migrate:status" class="btn">Status</a>
                <a href="?password={{ $password }}&action=run&cmd=migrate" class="btn warn">Migrate</a>
                <a href="?password={{ $password }}&action=run&cmd=db:seed" class="btn warn">Seed</a>
            </div>
        </div>

        <div class="action-card">
            <h3>📬 Queue</h3>
            <div class="hint">Jobs &amp; workers</div>
            <div class="btn-row">
                <a href="?password={{ $password }}&action=run&cmd=queue:work --stop-when-empty" class="btn">Process</a>
                <a href="?password={{ $password }}&action=run&cmd=queue:restart" class="btn">Restart Workers</a>
                <a href="?password={{ $password }}&action=run&cmd=queue:failed" class="btn">Failed Jobs</a>
            </div>
        </div>

        <div class="action-card">
            <h3>ℹ️ System Info</h3>
            <div class="hint">Inspect runtime state</div>
            <div class="btn-row">
                <a href="?password={{ $password }}&action=run&cmd=about" class="btn">About</a>
                <a href="?password={{ $password }}&action=run&cmd=route:list" class="btn">Routes</a>
                <a href="?password={{ $password }}&action=run&cmd=env" class="btn">Env</a>
            </div>
        </div>
    </section>

    <section class="action-card custom-form">
        <h3>⌨️ Custom Artisan Console</h3>
        <div class="hint">⚠️ Run any artisan command — omit the <code>php artisan</code> prefix. Shell metachars are blocked.</div>
        <form method="GET">
            <input type="hidden" name="password" value="{{ $password }}">
            <input type="hidden" name="action" value="run">
            <input type="text" name="custom_cmd" placeholder="e.g. make:controller MyController  ·  cache:clear --tags=posts" autocomplete="off" required>
            <button type="submit" class="btn primary block">▶ Execute Command</button>
        </form>
        <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border);">
            <div style="font-size: 11px; color: var(--text-3); letter-spacing: .12em; text-transform: uppercase; font-weight: 700; margin-bottom: 10px;">Quick Commands</div>
            <div class="btn-row">
                <a href="?password={{ $password }}&action=run&cmd=make:controller UserController" class="btn">make:controller</a>
                <a href="?password={{ $password }}&action=run&cmd=make:model Post -m" class="btn">make:model -m</a>
                <a href="?password={{ $password }}&action=run&cmd=make:migration create_posts_table" class="btn">make:migration</a>
                <a href="?password={{ $password }}&action=run&cmd=storage:link" class="btn">storage:link</a>
                <a href="?password={{ $password }}&action=run&cmd=key:generate" class="btn warn">key:generate</a>
            </div>
        </div>
    </section>

    <section class="panel" style="margin-top: 22px;">
        <div class="panel-header">
            <div class="panel-title">🔌 Network Interfaces</div>
            <div class="panel-hint">Live counters</div>
        </div>
        <div class="scrollx">
            <table class="iface-table" id="iface-table">
                <thead><tr><th>Interface</th><th>RX bytes</th><th>TX bytes</th></tr></thead>
                <tbody><tr><td colspan="3" style="color:var(--text-3);">Loading…</td></tr></tbody>
            </table>
        </div>
    </section>

</div>

<script>
const PASSWORD = @json($password);
const METRICS_URL = `?password=${encodeURIComponent(PASSWORD)}&action=metrics`;
const POLL_MS = 3000;
const HISTORY = 20;

function fmtBytes(b) {
    if (b == null || isNaN(b)) return '—';
    const u = ['B','KB','MB','GB','TB']; let i = 0; let n = Number(b);
    while (n >= 1024 && i < u.length - 1) { n /= 1024; i++; }
    return n.toFixed(n < 10 && i > 0 ? 2 : (n < 100 && i > 0 ? 1 : 0)) + ' ' + u[i];
}
function fmtRate(b) { return b == null ? '— /s' : fmtBytes(b) + '/s'; }
function fmtUptime(s) {
    if (s == null) return '—';
    const d = Math.floor(s / 86400);
    const h = Math.floor((s % 86400) / 3600);
    const m = Math.floor((s % 3600) / 60);
    if (d > 0) return `${d}d ${h}h`;
    if (h > 0) return `${h}h ${m}m`;
    return `${m}m`;
}
function pct(v) { return v == null ? '--' : v.toFixed(1); }

const chartOpts = (color1, color2) => ({
    responsive: true, maintainAspectRatio: false,
    animation: { duration: 400 },
    scales: {
        x: { display: false },
        y: { beginAtZero: true, ticks: { color: '#6b7196', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.05)' } },
    },
    plugins: {
        legend: { display: true, labels: { color: '#9aa0c7', font: { size: 11, weight: '600' }, boxWidth: 8, boxHeight: 8 } },
        tooltip: { backgroundColor: '#0f1120', borderColor: 'rgba(255,255,255,0.12)', borderWidth: 1, titleColor: '#e7e9f5', bodyColor: '#9aa0c7' },
    },
    elements: { point: { radius: 0 }, line: { tension: 0.35, borderWidth: 2 } },
});

const labels = Array.from({ length: HISTORY }, () => '');
const cpuData = Array(HISTORY).fill(null);
const ramData = Array(HISTORY).fill(null);
const rxData  = Array(HISTORY).fill(null);
const txData  = Array(HISTORY).fill(null);

const cpuChart = new Chart(document.getElementById('chart-cpu'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            { label: 'CPU %', data: cpuData, borderColor: '#7c5cff', backgroundColor: 'rgba(124,92,255,0.18)', fill: true },
            { label: 'RAM %', data: ramData, borderColor: '#4cc9f0', backgroundColor: 'rgba(76,201,240,0.16)', fill: true },
        ],
    },
    options: { ...chartOpts(), scales: { x: { display: false }, y: { beginAtZero: true, max: 100, ticks: { color: '#6b7196', font: { size: 10 }, callback: v => v + '%' }, grid: { color: 'rgba(255,255,255,0.05)' } } } },
});

const netChart = new Chart(document.getElementById('chart-net'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            { label: '↓ RX', data: rxData, borderColor: '#4cc9f0', backgroundColor: 'rgba(76,201,240,0.18)', fill: true },
            { label: '↑ TX', data: txData, borderColor: '#f472b6', backgroundColor: 'rgba(244,114,182,0.16)', fill: true },
        ],
    },
    options: chartOpts(),
});

async function poll() {
    try {
        const res = await fetch(METRICS_URL, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const d = await res.json();
        apply(d);
        document.getElementById('m-live').textContent = 'LIVE';
    } catch (e) {
        document.getElementById('m-live').textContent = 'OFFLINE';
        console.error(e);
    }
}

function apply(d) {
    const cpu = d.cpu || {}, ram = d.memory || {}, disk = d.disk || {}, net = d.network || {}, ses = d.sessions || {}, php = d.php || {}, app = d.app || {};

    const cpuV = cpu.usage ?? cpu.load_pc;
    setText('m-cpu', pct(cpuV));
    setBar('m-cpu-bar', cpuV);
    setText('m-cpu-cores', (cpu.cores ?? '—') + ' cores');
    setText('m-cpu-load', cpu.load && cpu.load['1m'] != null ? `load ${cpu.load['1m']}` : 'load —');

    setText('m-ram', pct(ram.percent));
    setBar('m-ram-bar', ram.percent);
    setText('m-ram-used', fmtBytes(ram.used) + ' used');
    setText('m-ram-total', fmtBytes(ram.total) + ' total');

    setText('m-disk', pct(disk.percent));
    setBar('m-disk-bar', disk.percent);
    setText('m-disk-used', fmtBytes(disk.used) + ' used');
    setText('m-disk-free', fmtBytes(disk.free) + ' free');

    const netAvailable = net.rx_bytes != null || net.tx_bytes != null;
    if (netAvailable) {
        const totalRate = (net.rx_rate || 0) + (net.tx_rate || 0);
        setText('m-net', fmtBytes(totalRate));
        setBar('m-net-bar', Math.min(100, totalRate / (10 * 1024 * 1024) * 100));
        setText('m-net-rx', '↓ ' + fmtRate(net.rx_rate));
        setText('m-net-tx', '↑ ' + fmtRate(net.tx_rate));
        setText('net-rx-rate', fmtRate(net.rx_rate));
        setText('net-tx-rate', fmtRate(net.tx_rate));
        setText('net-rx-total', 'total ' + fmtBytes(net.rx_bytes));
        setText('net-tx-total', 'total ' + fmtBytes(net.tx_bytes));
    } else {
        setText('m-net', 'N/A');
        setBar('m-net-bar', 0);
        setText('m-net-rx', '/proc unavailable');
        setText('m-net-tx', 'host isolated');
        setText('net-rx-rate', 'N/A');
        setText('net-tx-rate', 'N/A');
        setText('net-rx-total', '/proc not mounted');
        setText('net-tx-total', '');
    }

    setText('m-users', ses.active_5m ?? '0');
    setText('m-users-logged', (ses.logged_in ?? 0) + ' logged-in');
    setText('m-users-total', (ses.total ?? 0) + ' total sess');

    setText('m-uptime', app.uptime != null ? fmtUptime(app.uptime) : 'N/A');
    setText('m-queue', 'queue ' + (app.queue_jobs ?? '—'));
    setText('m-failed', 'failed ' + (app.queue_failed ?? '—'));

    setText('m-os', app.os ?? '—');
    setText('m-host', app.hostname ?? '—');
    setText('m-laravel', app.laravel ?? '—');
    setText('m-php', php.version ?? '—');

    setText('i-env', app.environment ?? '—');
    setText('i-debug', app.debug ? 'ON ⚠' : 'off');
    setText('i-tz', app.timezone ?? '—');
    setText('i-mlimit', php.memory_limit === -1 ? '∞' : fmtBytes(php.memory_limit));
    setText('i-upmax', fmtBytes(php.upload_max));
    setText('i-opcache', php.opcache_enabled ? 'enabled ✓' : 'disabled');
    setText('i-sess', ses.driver ?? '—');
    setText('i-ts', new Date(d.ts).toLocaleTimeString());

    pushPoint(cpuData, cpuV);
    pushPoint(ramData, ram.percent);
    pushPoint(rxData, net.rx_rate);
    pushPoint(txData, net.tx_rate);
    cpuChart.update('none');
    netChart.update('none');

    const tbody = document.querySelector('#iface-table tbody');
    if (net.interfaces && Object.keys(net.interfaces).length) {
        tbody.innerHTML = Object.entries(net.interfaces)
            .map(([n, r]) => `<tr><td>${escapeHtml(n)}</td><td>${fmtBytes(r.rx)}</td><td>${fmtBytes(r.tx)}</td></tr>`)
            .join('');
    } else {
        tbody.innerHTML = '<tr><td colspan="3" style="color:var(--text-3);">No interface data available on this host</td></tr>';
    }
}

function setText(id, v) { const el = document.getElementById(id); if (el) el.textContent = v; }
function setBar(id, v) {
    const el = document.getElementById(id); if (!el) return;
    const p = Math.max(0, Math.min(100, Number(v) || 0));
    el.style.width = p + '%';
    if (p >= 90)      el.style.background = 'linear-gradient(90deg,#f87171,#fbbf24)';
    else if (p >= 70) el.style.background = 'linear-gradient(90deg,#fbbf24,#7c5cff)';
    else              el.style.background = 'linear-gradient(90deg,#7c5cff,#4cc9f0)';
}
function pushPoint(arr, v) { arr.push(v == null ? null : Number(v)); if (arr.length > HISTORY) arr.shift(); }
function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

poll();
setInterval(poll, POLL_MS);
</script>
</body>
</html>
