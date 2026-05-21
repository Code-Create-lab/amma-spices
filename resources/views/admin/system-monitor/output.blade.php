<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Command Output · Sys Monitor</title>
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
        --danger: #f87171;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        padding: 28px;
    }
    body::before {
        content: '';
        position: fixed; inset: 0;
        background:
            radial-gradient(800px 500px at 10% 0%, rgba(124,92,255,0.25), transparent 60%),
            radial-gradient(700px 500px at 90% 0%, rgba(76,201,240,0.18), transparent 60%);
        pointer-events: none;
        z-index: 0;
    }
    .container { position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; }
    .header-card {
        background: var(--panel);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 22px;
        margin-bottom: 18px;
        box-shadow: 0 18px 60px rgba(0,0,0,0.4);
    }
    .status {
        display: inline-flex; align-items: center; gap: 10px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 14px;
    }
    .status.success { background: rgba(45,212,191,0.14); border: 1px solid rgba(45,212,191,0.35); color: var(--success); }
    .status.error   { background: rgba(248,113,113,0.14); border: 1px solid rgba(248,113,113,0.35); color: var(--danger); }
    .cmd-row {
        display: grid; grid-template-columns: auto 1fr auto; gap: 16px;
        align-items: center;
        padding: 14px 18px;
        background: rgba(0,0,0,0.32);
        border: 1px solid var(--border);
        border-radius: 12px;
        border-left: 3px solid var(--accent);
    }
    .cmd-row .label { font-size: 11px; color: var(--text-3); font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .cmd-row .cmd { font-family: 'JetBrains Mono', monospace; font-size: 14px; font-weight: 600; word-break: break-all; }
    .cmd-row .ms { font-family: 'JetBrains Mono', monospace; font-size: 13px; color: var(--text-2); white-space: nowrap; }
    .output-pane {
        background: var(--panel);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 18px 60px rgba(0,0,0,0.4);
    }
    .output-pane .head {
        padding: 12px 18px;
        border-bottom: 1px solid var(--border);
        display: flex; justify-content: space-between; align-items: center;
        font-size: 12px; color: var(--text-3); font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
    }
    .output-pane pre {
        margin: 0;
        padding: 22px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
        line-height: 1.6;
        white-space: pre-wrap;
        word-break: break-word;
        max-height: 70vh;
        overflow: auto;
        color: var(--text);
    }
    .actions { margin-top: 18px; display: flex; gap: 10px; flex-wrap: wrap; }
    .btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 10px 16px;
        background: rgba(255,255,255,0.06);
        border: 1px solid var(--border);
        color: var(--text);
        border-radius: 10px;
        font-size: 13px; font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all .2s;
    }
    .btn:hover { background: rgba(255,255,255,0.12); border-color: var(--border-2); }
    .btn.primary { background: linear-gradient(135deg, #7c5cff, #4cc9f0); border-color: transparent; }
    .dot-traffic { display: inline-flex; gap: 5px; }
    .dot-traffic span { width: 10px; height: 10px; border-radius: 50%; }
    .dot-traffic span:nth-child(1) { background: #ff5f57; }
    .dot-traffic span:nth-child(2) { background: #febc2e; }
    .dot-traffic span:nth-child(3) { background: #28c840; }
</style>
</head>
<body>

<div class="container">

    <div class="header-card">
        <div class="status {{ $status }}">
            @if ($status === 'success') ✅ Success @else ❌ Failed @endif
        </div>
        <div class="cmd-row">
            <div>
                <div class="label">Command</div>
            </div>
            <div class="cmd">php artisan {{ $cmd }}</div>
            <div class="ms">⏱ {{ $ms }} ms</div>
        </div>
    </div>

    <div class="output-pane">
        <div class="head">
            <div class="dot-traffic"><span></span><span></span><span></span></div>
            <span>stdout</span>
        </div>
        <pre>{{ $output ?: 'No output' }}</pre>
    </div>

    <div class="actions">
        <a href="?password={{ $password }}" class="btn primary">← Back to Dashboard</a>
        <a href="?password={{ $password }}&action=logs" class="btn">📖 View Logs</a>
    </div>

</div>

</body>
</html>
