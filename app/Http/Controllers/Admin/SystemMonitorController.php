<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemMonitorController extends Controller
{
    protected function gate(Request $request): void
    {
        $expected = (string) env('ADMIN_PANEL_PASSWORD', '');
        if ($expected === '' || strlen($expected) < 16) {
            abort(503, 'System monitor disabled: ADMIN_PANEL_PASSWORD must be set to a value of at least 16 characters in .env');
        }
        $given = (string) $request->query('password', '');
        if ($given === '' || !hash_equals($expected, $given)) {
            abort(403, 'Access Denied');
        }
    }

    public function dashboard(Request $request)
    {
        $this->gate($request);
        return view('admin.system-monitor.dashboard', [
            'password' => $request->query('password'),
        ]);
    }

    public function debug(Request $request)
    {
        $this->gate($request);

        $probe = function (string $cmd) {
            if (!$this->canShell()) return ['ok' => false, 'reason' => 'shell_exec disabled'];
            $out = @shell_exec($cmd . ' 2>&1');
            return ['ok' => $out !== null && $out !== '', 'output' => $out ? substr($out, 0, 600) : null];
        };
        $readFile = function (string $path) {
            $ok = @is_readable($path);
            $size = $ok ? @filesize($path) : null;
            $head = $ok ? @file_get_contents($path, false, null, 0, 400) : null;
            return ['readable' => $ok, 'size' => $size, 'head' => $head];
        };

        return response()->json([
            'os'                => PHP_OS_FAMILY,
            'php_version'       => PHP_VERSION,
            'open_basedir'      => ini_get('open_basedir') ?: null,
            'disable_functions' => ini_get('disable_functions') ?: null,
            'shell_exec'        => $this->canShell(),
            'sys_getloadavg'    => function_exists('sys_getloadavg'),
            'proc_meminfo'      => $readFile('/proc/meminfo'),
            'proc_stat'         => $readFile('/proc/stat'),
            'proc_uptime'       => $readFile('/proc/uptime'),
            'proc_net_dev'      => $readFile('/proc/net/dev'),
            'shell_free'        => $probe('free -b'),
            'shell_uptime'      => $probe('cat /proc/uptime'),
            'shell_uname'       => $probe('uname -a'),
            'shell_nproc'       => $probe('nproc'),
            'metrics_now'       => [
                'memory' => $this->memoryMetrics(),
                'cpu'    => $this->cpuMetrics(),
                'net'    => $this->networkMetrics(),
                'uptime' => $this->systemUptime(),
            ],
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function metrics(Request $request)
    {
        $this->gate($request);

        $cpu      = $this->cpuMetrics();
        $memory   = $this->memoryMetrics();
        $disk     = $this->diskMetrics();
        $net      = $this->networkMetrics();
        $sessions = $this->sessionMetrics();
        $php      = [
            'version'         => PHP_VERSION,
            'memory_usage'    => memory_get_usage(true),
            'memory_peak'     => memory_get_peak_usage(true),
            'memory_limit'    => $this->phpIniBytes('memory_limit'),
            'upload_max'      => $this->phpIniBytes('upload_max_filesize'),
            'post_max'        => $this->phpIniBytes('post_max_size'),
            'max_execution'   => (int) ini_get('max_execution_time'),
            'opcache_enabled' => function_exists('opcache_get_status') && @opcache_get_status(false) !== false,
        ];
        $app = [
            'laravel'     => app()->version(),
            'environment' => app()->environment(),
            'debug'       => config('app.debug'),
            'timezone'    => config('app.timezone'),
            'uptime'      => $this->systemUptime(),
            'os'          => PHP_OS_FAMILY,
            'hostname'    => gethostname(),
            'load_avg'    => $this->loadAverage(),
            'queue_failed'=> $this->safeCount('failed_jobs'),
            'queue_jobs'  => $this->safeCount('jobs'),
        ];

        return response()->json([
            'ts'       => now()->toIso8601String(),
            'cpu'      => $cpu,
            'memory'   => $memory,
            'disk'     => $disk,
            'network'  => $net,
            'sessions' => $sessions,
            'php'      => $php,
            'app'      => $app,
        ]);
    }

    public function logs(Request $request)
    {
        $this->gate($request);

        $lines  = (int) $request->query('lines', 500);
        $search = $request->query('search');
        $logDir = storage_path('logs');

        $availableLogs = [];
        foreach (glob($logDir . '/laravel-*.log') ?: [] as $file) {
            if (preg_match('/laravel-(\d{4}-\d{2}-\d{2})\.log$/', $file, $m)) {
                $availableLogs[] = $m[1];
            }
        }
        rsort($availableLogs);

        $hasLegacy = file_exists($logDir . '/laravel.log');
        $defaultLog = $hasLegacy ? 'legacy' : ($availableLogs[0] ?? '');
        $selectedDate = $request->query('log_date', $defaultLog);

        if ($selectedDate === 'legacy') {
            $logFile = $logDir . '/laravel.log';
        } elseif ($selectedDate) {
            $logFile = $logDir . '/laravel-' . $selectedDate . '.log';
        } else {
            $logFile = null;
        }

        $logs = [];
        if ($logFile && file_exists($logFile)) {
            $logs = $this->tailFile($logFile, $lines);
            $logs = array_reverse($logs);
            if ($search) {
                $logs = array_values(array_filter($logs, fn($l) => stripos($l, $search) !== false));
            }
        } elseif ($logFile) {
            $logs = ['Log file not found for the selected date!'];
        }

        return view('admin.system-monitor.logs', [
            'password'      => $request->query('password'),
            'logs'          => $logs,
            'lines'         => $lines,
            'search'        => $search,
            'availableLogs' => $availableLogs,
            'hasLegacy'     => $hasLegacy,
            'selectedDate'  => $selectedDate,
        ]);
    }

    public function runCommand(Request $request)
    {
        $this->gate($request);

        $cmd = trim((string) ($request->query('custom_cmd') ?: $request->query('cmd')));
        if ($cmd === '') {
            return response('<h2 style="color:red;">No command provided!</h2>', 400);
        }

        $this->guardCommand($cmd);

        $start = microtime(true);
        $status = 'success';
        try {
            Artisan::call($cmd);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            $status = 'error';
            $output = $e->getMessage();
        }
        $ms = round((microtime(true) - $start) * 1000, 2);

        return view('admin.system-monitor.output', [
            'password' => $request->query('password'),
            'cmd'      => $cmd,
            'output'   => $output,
            'status'   => $status,
            'ms'       => $ms,
        ]);
    }

    public function clearSessions(Request $request)
    {
        $this->gate($request);

        $deleted = 0;
        $errors  = [];
        $driver  = config('session.driver');

        try {
            if ($driver === 'file') {
                $path = config('session.files', storage_path('framework/sessions'));
                if (is_dir($path)) {
                    foreach (glob(rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                        if (is_file($file) && basename($file) !== '.gitignore') {
                            if (@unlink($file)) $deleted++;
                            else $errors[] = 'Could not delete: ' . $file;
                        }
                    }
                } else {
                    $errors[] = 'Session path not found: ' . $path;
                }
            } elseif ($driver === 'database') {
                $deleted = DB::table(config('session.table', 'sessions'))->delete();
            } else {
                Cache::store(config('session.connection'))->flush();
                $deleted = -1;
            }

            foreach ([
                base_path('bootstrap/cache/config.php'),
                base_path('bootstrap/cache/routes-v7.php'),
                base_path('bootstrap/cache/routes.php'),
                base_path('bootstrap/cache/services.php'),
                base_path('bootstrap/cache/packages.php'),
            ] as $boot) {
                if (file_exists($boot)) @unlink($boot);
            }
        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }

        return view('admin.system-monitor.output', [
            'password' => $request->query('password'),
            'cmd'      => 'clear_sessions',
            'output'   => 'Cleared ' . ($deleted >= 0 ? $deleted : 'all') . ' session record(s). Bootstrap cache flushed.'
                          . (count($errors) ? "\n\nErrors:\n" . implode("\n", $errors) : ''),
            'status'   => count($errors) ? 'error' : 'success',
            'ms'       => 0,
        ]);
    }

    // ====================== METRIC HELPERS ======================

    protected function cpuMetrics(): array
    {
        $cores = $this->cpuCoreCount();
        $usage = $this->cpuUsagePercent();
        $load  = $this->loadAverage();

        return [
            'cores'   => $cores,
            'usage'   => $usage,
            'load'    => $load,
            'load_pc' => $cores > 0 && $load['1m'] !== null ? round(($load['1m'] / $cores) * 100, 2) : null,
        ];
    }

    protected function cpuUsagePercent(): ?float
    {
        $os = PHP_OS_FAMILY;
        try {
            if ($os === 'Linux') {
                $sample1 = $this->procStatSnapshot();
                if ($sample1) {
                    usleep(200000);
                    $sample2 = $this->procStatSnapshot();
                    if ($sample2) {
                        $totalDelta = $sample2['total'] - $sample1['total'];
                        $idleDelta  = $sample2['idle']  - $sample1['idle'];
                        if ($totalDelta > 0) return round((1 - ($idleDelta / $totalDelta)) * 100, 2);
                    }
                }
                // Fallback: `top` or `vmstat`
                if ($this->canShell()) {
                    $out = @shell_exec("top -bn1 2>/dev/null | grep -i '%Cpu'");
                    if ($out && preg_match('/(\d+\.\d+)\s*id/', $out, $m)) {
                        return round(100 - (float) $m[1], 2);
                    }
                }
                return null;
            }
            if ($os === 'Windows') {
                $out = @shell_exec('wmic cpu get loadpercentage /value 2>NUL');
                if ($out && preg_match('/LoadPercentage=(\d+)/', $out, $m)) {
                    return (float) $m[1];
                }
            }
            if ($os === 'Darwin') {
                $out = @shell_exec("top -l 1 -n 0 | grep 'CPU usage'");
                if ($out && preg_match('/(\d+\.\d+)% user.*?(\d+\.\d+)% sys/', $out, $m)) {
                    return round((float)$m[1] + (float)$m[2], 2);
                }
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    protected function procStatSnapshot(): ?array
    {
        $contents = @file_get_contents('/proc/stat');
        if (!$contents) return null;
        $line = strtok($contents, "\n");
        if (!$line) return null;
        $parts = preg_split('/\s+/', trim($line));
        array_shift($parts);
        $vals = array_map('intval', $parts);
        $idle = ($vals[3] ?? 0) + ($vals[4] ?? 0);
        return ['total' => array_sum($vals), 'idle' => $idle];
    }

    protected function cpuCoreCount(): int
    {
        if (function_exists('shell_exec')) {
            if (PHP_OS_FAMILY === 'Linux') {
                $n = (int) @shell_exec('nproc 2>/dev/null');
                if ($n > 0) return $n;
            }
            if (PHP_OS_FAMILY === 'Windows') {
                $out = (int) @getenv('NUMBER_OF_PROCESSORS');
                if ($out > 0) return $out;
            }
        }
        return 1;
    }

    protected function loadAverage(): array
    {
        if (function_exists('sys_getloadavg')) {
            $la = sys_getloadavg();
            if (is_array($la)) {
                return ['1m' => round($la[0], 2), '5m' => round($la[1], 2), '15m' => round($la[2], 2)];
            }
        }
        return ['1m' => null, '5m' => null, '15m' => null];
    }

    protected function memoryMetrics(): array
    {
        $total = $free = $used = null;
        $source = null;
        $os = PHP_OS_FAMILY;

        try {
            if ($os === 'Linux') {
                // Primary: /proc/meminfo (file_get_contents bypasses some open_basedir setups
                // that block file() / is_readable on /proc).
                $contents = @file_get_contents('/proc/meminfo');
                if ($contents) {
                    $info = [];
                    foreach (preg_split('/\r?\n/', $contents) as $line) {
                        if (preg_match('/^(\w+):\s+(\d+)(?:\s+kB)?/i', $line, $m)) {
                            $info[$m[1]] = (int) $m[2] * 1024;
                        }
                    }
                    $total = $info['MemTotal'] ?? null;
                    $avail = $info['MemAvailable'] ?? $info['MemFree'] ?? null;
                    if ($total !== null && $avail !== null) {
                        $free   = $avail;
                        $used   = $total - $avail;
                        $source = '/proc/meminfo';
                    }
                }

                // Fallback: `free -b` if /proc blocked by open_basedir
                if ($total === null && $this->canShell()) {
                    $out = @shell_exec('free -b 2>/dev/null');
                    if ($out && preg_match('/Mem:\s+(\d+)\s+(\d+)\s+(\d+)(?:\s+\d+\s+\d+\s+(\d+))?/', $out, $m)) {
                        $total = (int) $m[1];
                        $used  = (int) $m[2];
                        $free  = isset($m[4]) ? (int) $m[4] : (int) $m[3];
                        $source = 'free -b';
                    }
                }

                // Last resort: parse PHP's own RSS to give SOME number
                if ($total === null && $this->canShell()) {
                    $out = @shell_exec('cat /proc/self/status 2>/dev/null | grep -E "VmRSS|VmPeak"');
                    if ($out && preg_match('/VmRSS:\s+(\d+)/', $out, $m)) {
                        $used = (int) $m[1] * 1024;
                        $source = '/proc/self/status (process only)';
                    }
                }

                // Final fallback: show PHP process memory against memory_limit
                // (chroot/CageFS hosts hide /proc — host RAM is unreachable)
                if ($total === null) {
                    $limit = $this->phpIniBytes('memory_limit');
                    if ($limit && $limit > 0) {
                        $total  = $limit;
                        $used   = memory_get_usage(true);
                        $free   = max(0, $total - $used);
                        $source = 'PHP process (chroot — host RAM unreachable)';
                    }
                }
            } elseif ($os === 'Windows') {
                $out = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /value 2>NUL');
                if ($out) {
                    if (preg_match('/FreePhysicalMemory=(\d+)/', $out, $f))      $free  = ((int)$f[1]) * 1024;
                    if (preg_match('/TotalVisibleMemorySize=(\d+)/', $out, $t))  $total = ((int)$t[1]) * 1024;
                    if ($total && $free !== null) {
                        $used = $total - $free;
                        $source = 'wmic';
                    }
                }
            } elseif ($os === 'Darwin' && $this->canShell()) {
                $out = @shell_exec('sysctl -n hw.memsize 2>/dev/null');
                if ($out) $total = (int) trim($out);
                $vm = @shell_exec('vm_stat 2>/dev/null');
                if ($vm) {
                    $page = 4096;
                    if (preg_match('/page size of (\d+)/', $vm, $p)) $page = (int) $p[1];
                    if (preg_match('/Pages free:\s+(\d+)/', $vm, $f1) && preg_match('/Pages speculative:\s+(\d+)/', $vm, $f2)) {
                        $free = ((int)$f1[1] + (int)$f2[1]) * $page;
                    }
                    if ($total && $free !== null) {
                        $used = $total - $free;
                        $source = 'sysctl+vm_stat';
                    }
                }
            }
        } catch (\Throwable $e) {}

        return [
            'total'   => $total,
            'used'    => $used,
            'free'    => $free,
            'percent' => ($total && $used !== null) ? round(($used / $total) * 100, 2) : null,
            'source'  => $source,
        ];
    }

    protected function canShell(): bool
    {
        if (!function_exists('shell_exec')) return false;
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        return !in_array('shell_exec', $disabled, true);
    }

    protected function diskMetrics(): array
    {
        $root  = base_path();
        $total = @disk_total_space($root) ?: null;
        $free  = @disk_free_space($root) ?: null;
        $used  = ($total && $free !== null) ? $total - $free : null;

        return [
            'path'    => $root,
            'total'   => $total,
            'used'    => $used,
            'free'    => $free,
            'percent' => ($total && $used !== null) ? round(($used / $total) * 100, 2) : null,
        ];
    }

    protected function networkMetrics(): array
    {
        $current = $this->readNicCounters();
        if (!$current) {
            return ['rx_bytes' => null, 'tx_bytes' => null, 'rx_rate' => null, 'tx_rate' => null, 'interfaces' => []];
        }

        $prev = Cache::get('sysmon:nic_snapshot');
        $now  = microtime(true);
        Cache::put('sysmon:nic_snapshot', ['ts' => $now, 'data' => $current], 600);

        $rxRate = $txRate = null;
        if ($prev && isset($prev['ts'], $prev['data'])) {
            $dt = max($now - $prev['ts'], 0.001);
            $prevTotals = $this->sumNicCounters($prev['data']);
            $curTotals  = $this->sumNicCounters($current);
            $rxRate = max(0, round(($curTotals['rx'] - $prevTotals['rx']) / $dt));
            $txRate = max(0, round(($curTotals['tx'] - $prevTotals['tx']) / $dt));
        }

        $totals = $this->sumNicCounters($current);
        return [
            'rx_bytes'   => $totals['rx'],
            'tx_bytes'   => $totals['tx'],
            'rx_rate'    => $rxRate,
            'tx_rate'    => $txRate,
            'interfaces' => $current,
        ];
    }

    protected function sumNicCounters(array $ifaces): array
    {
        $rx = 0; $tx = 0;
        foreach ($ifaces as $name => $row) {
            if (preg_match('/^(lo|Loopback)/i', $name)) continue;
            $rx += $row['rx'] ?? 0;
            $tx += $row['tx'] ?? 0;
        }
        return ['rx' => $rx, 'tx' => $tx];
    }

    protected function readNicCounters(): array
    {
        $os = PHP_OS_FAMILY;
        $out = [];

        try {
            if ($os === 'Linux') {
                $contents = @file_get_contents('/proc/net/dev');
                if (!$contents && $this->canShell()) {
                    $contents = @shell_exec('cat /proc/net/dev 2>/dev/null');
                }
                if ($contents) {
                    $lines = preg_split('/\r?\n/', $contents);
                    foreach ($lines as $i => $line) {
                        if ($i < 2) continue;
                        if (!preg_match('/^\s*([\w@:.\-]+):\s*(.+)$/', $line, $m)) continue;
                        $name   = $m[1];
                        $fields = preg_split('/\s+/', trim($m[2]));
                        $out[$name] = ['rx' => (int) $fields[0], 'tx' => (int) ($fields[8] ?? 0)];
                    }
                }
            } elseif ($os === 'Windows') {
                $raw = @shell_exec('wmic path Win32_PerfRawData_Tcpip_NetworkInterface get Name,BytesReceivedPersec,BytesSentPersec /format:csv 2>NUL');
                if ($raw) {
                    $lines = preg_split('/\r?\n/', trim($raw));
                    $headers = null;
                    foreach ($lines as $line) {
                        if ($line === '') continue;
                        $cols = str_getcsv($line);
                        if ($headers === null) { $headers = $cols; continue; }
                        $row = array_combine($headers, $cols) ?: [];
                        $name = $row['Name'] ?? null;
                        if (!$name) continue;
                        $out[$name] = [
                            'rx' => (int) ($row['BytesReceivedPersec'] ?? 0),
                            'tx' => (int) ($row['BytesSentPersec'] ?? 0),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        return $out;
    }

    protected function sessionMetrics(): array
    {
        $driver = config('session.driver');
        $window = (int) config('session.lifetime', 120) * 60;
        $now    = time();
        $active = 0;
        $loggedIn = 0;
        $total = 0;

        try {
            if ($driver === 'file') {
                $path = config('session.files', storage_path('framework/sessions'));
                if (is_dir($path)) {
                    foreach (glob(rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                        if (!is_file($file) || basename($file) === '.gitignore') continue;
                        $total++;
                        $mtime = @filemtime($file);
                        if ($mtime && ($now - $mtime) <= 300) {
                            $active++;
                            $payload = @file_get_contents($file);
                            if ($payload && stripos($payload, 'login_') !== false) $loggedIn++;
                        }
                    }
                }
            } elseif ($driver === 'database') {
                $tbl = config('session.table', 'sessions');
                $total  = DB::table($tbl)->count();
                $active = DB::table($tbl)->where('last_activity', '>=', $now - 300)->count();
                $loggedIn = DB::table($tbl)
                    ->where('last_activity', '>=', $now - 300)
                    ->whereNotNull('user_id')
                    ->count();
            }
        } catch (\Throwable $e) {}

        return [
            'driver'    => $driver,
            'total'     => $total,
            'active_5m' => $active,
            'logged_in' => $loggedIn,
            'window_s'  => 300,
        ];
    }

    protected function systemUptime(): ?int
    {
        try {
            if (PHP_OS_FAMILY === 'Linux') {
                $u = @file_get_contents('/proc/uptime');
                if ($u) return (int) floatval(explode(' ', $u)[0]);
                if ($this->canShell()) {
                    $out = @shell_exec('cat /proc/uptime 2>/dev/null');
                    if ($out) return (int) floatval(explode(' ', $out)[0]);
                    $out = @shell_exec('awk \'{print $1}\' /proc/uptime 2>/dev/null');
                    if ($out) return (int) floatval(trim($out));
                }
            }
            if (PHP_OS_FAMILY === 'Windows') {
                $out = @shell_exec('wmic OS get LastBootUpTime /value 2>NUL');
                if ($out && preg_match('/LastBootUpTime=([\d.]+)/', $out, $m)) {
                    $ts = \DateTime::createFromFormat('YmdHis.uO', substr($m[1], 0, 22) . substr($m[1], -5));
                    if ($ts) return time() - $ts->getTimestamp();
                }
            }
        } catch (\Throwable $e) {}
        return null;
    }

    protected function safeCount(string $table): ?int
    {
        try {
            if (Schema::hasTable($table)) return DB::table($table)->count();
        } catch (\Throwable $e) {}
        return null;
    }

    protected function phpIniBytes(string $key): ?int
    {
        $val = ini_get($key);
        if ($val === false || $val === '') return null;
        $val = trim($val);
        if ($val === '-1') return -1;
        $unit = strtolower(substr($val, -1));
        $num  = (int) $val;
        return match ($unit) {
            'g'     => $num * 1024 * 1024 * 1024,
            'm'     => $num * 1024 * 1024,
            'k'     => $num * 1024,
            default => $num,
        };
    }

    protected function tailFile(string $path, int $lines): array
    {
        $fp = @fopen($path, 'rb');
        if (!$fp) return [];
        $buffer = '';
        $chunk  = 8192;
        $count  = 0;
        $pos    = -1;
        fseek($fp, 0, SEEK_END);
        $end = ftell($fp);
        while ($end + $pos > 0 && $count <= $lines) {
            $read = min($chunk, $end + $pos);
            $pos -= $read;
            fseek($fp, max($end + $pos, 0));
            $buffer = fread($fp, $read) . $buffer;
            $count = substr_count($buffer, "\n");
        }
        fclose($fp);
        $arr = explode("\n", $buffer);
        return array_slice($arr, -$lines);
    }

    protected function guardCommand(string $cmd): void
    {
        $blocked = ['env:set', 'env:encrypt', 'env:decrypt', 'down'];
        foreach ($blocked as $b) {
            if (stripos($cmd, $b) === 0) abort(403, "Command '{$b}' is blocked.");
        }
        if (preg_match('/[;&|`$]/', $cmd)) abort(400, 'Shell metacharacters not allowed.');
    }
}
