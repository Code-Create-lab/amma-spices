<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Laravel Logs · Sys Monitor</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<style>
    :root {
        --bg: #0a0b14;
        --panel: rgba(20, 22, 38, 0.7);
        --border: rgba(255,255,255,0.08);
        --border-2: rgba(255,255,255,0.16);
        --text: #e7e9f5;
        --text-2: #9aa0c7;
        --text-3: #6b7196;
        --accent: #7c5cff;
        --accent-2: #4cc9f0;
        --success: #2dd4bf;
        --warn: #fbbf24;
        --danger: #f87171;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
    }
    body::before {
        content: '';
        position: fixed; inset: 0;
        background:
            radial-gradient(800px 500px at 5% 0%, rgba(124,92,255,0.25), transparent 60%),
            radial-gradient(700px 500px at 95% 0%, rgba(76,201,240,0.15), transparent 60%);
        pointer-events: none;
        z-index: 0;
    }
    .topbar {
        position: sticky; top: 0; z-index: 10;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        background: rgba(10, 11, 20, 0.78);
        border-bottom: 1px solid var(--border);
        padding: 14px 22px;
        display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
    }
    .topbar .title { font-size: 14px; font-weight: 700; margin-right: 18px; letter-spacing: -0.01em; display: flex; align-items: center; gap: 8px; }
    .topbar .title .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--success); animation: pulse 2s infinite; }
    @keyframes pulse {
        0%   { box-shadow: 0 0 0 0 rgba(45,212,191,0.5); }
        70%  { box-shadow: 0 0 0 8px rgba(45,212,191,0); }
        100% { box-shadow: 0 0 0 0 rgba(45,212,191,0); }
    }
    .topbar form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .topbar input[type="text"], .topbar select {
        padding: 9px 12px;
        background: rgba(0,0,0,0.32);
        border: 1px solid var(--border);
        color: var(--text);
        border-radius: 8px;
        font-size: 13px;
        font-family: 'JetBrains Mono', monospace;
    }
    .topbar input[type="text"]:focus, .topbar select:focus {
        outline: none; border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(124,92,255,0.18);
    }
    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 14px;
        background: rgba(255,255,255,0.06);
        border: 1px solid var(--border);
        color: var(--text);
        border-radius: 8px;
        font-size: 13px; font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all .2s;
        font-family: inherit;
    }
    .btn:hover { background: rgba(255,255,255,0.12); border-color: var(--border-2); }
    .btn.primary { background: linear-gradient(135deg, #7c5cff, #4cc9f0); border-color: transparent; }
    .badge {
        padding: 5px 11px;
        background: rgba(251,191,36,0.14);
        border: 1px solid rgba(251,191,36,0.32);
        color: var(--warn);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        font-family: 'JetBrains Mono', monospace;
    }
    .container { position: relative; z-index: 1; padding: 22px; }
    .log-pane {
        background: var(--panel);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 18px 60px rgba(0,0,0,0.4);
    }
    .log-pane pre {
        margin: 0;
        padding: 18px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 12.5px;
        line-height: 1.55;
        overflow: auto;
        max-height: calc(100vh - 160px);
        white-space: pre-wrap;
        word-break: break-word;
    }
    .log-pane pre .error    { color: var(--danger); }
    .log-pane pre .warning  { color: var(--warn); }
    .log-pane pre .info     { color: var(--accent-2); }
    .empty { padding: 60px 20px; text-align: center; color: var(--text-3); font-size: 14px; }
    .footer-meta { font-size: 12px; color: var(--text-3); margin-left: auto; font-family: 'JetBrains Mono', monospace; }
</style>
</head>
<body>

<header class="topbar">
    <div class="title"><span class="dot"></span> Application Logs</div>
    <form method="GET">
        <input type="hidden" name="password" value="{{ $password }}">
        <input type="hidden" name="action" value="logs">
        <select name="log_date" onchange="this.form.submit()" title="Select log date">
            @if ($hasLegacy)
                <option value="legacy" @selected($selectedDate === 'legacy')>laravel.log · legacy</option>
            @endif
            @forelse ($availableLogs as $date)
                <option value="{{ $date }}" @selected($selectedDate === $date)>
                    {{ $date }}{{ $date === ($availableLogs[0] ?? '') ? ' · today' : '' }}
                </option>
            @empty
            @endforelse
            @if (empty($availableLogs) && !$hasLegacy)
                <option value="">No log files</option>
            @endif
        </select>
        <input type="text" name="search" placeholder="🔍 Search logs…" value="{{ $search }}">
        <select name="lines" title="Lines to load">
            @foreach ([200, 500, 1000, 2000] as $n)
                <option value="{{ $n }}" {{ (int) $lines === $n ? 'selected' : '' }}>{{ $n }} lines</option>
            @endforeach
        </select>
        <button type="submit" class="btn primary">Filter</button>
    </form>
    <button onclick="location.reload()" class="btn">↻ Refresh</button>
    <a href="?password={{ $password }}" class="btn">← Dashboard</a>
    @if ($selectedDate && $selectedDate !== 'legacy')
        <span class="badge">📅 {{ $selectedDate }}</span>
    @endif
    <span class="footer-meta">{{ count($logs) }} entries</span>
</header>

<main class="container">
    <div class="log-pane">
        @if (empty($logs))
            <div class="empty">No log entries to display.</div>
        @else
            <pre>@foreach ($logs as $log)
@php
    $class = '';
    if (stripos($log, 'error') !== false)        $class = 'error';
    elseif (stripos($log, 'warning') !== false)  $class = 'warning';
    elseif (stripos($log, 'info') !== false)     $class = 'info';
@endphp<span class="{{ $class }}">{{ $log }}</span>
@endforeach</pre>
        @endif
    </div>
</main>

</body>
</html>
