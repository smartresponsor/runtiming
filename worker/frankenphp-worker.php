<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);



use App\Kernel;
use App\Service\Runtime\RuntimeTelemetryFileSink;
use App\Service\Runtime\RuntimeTelemetryJsonCodec;
use App\ServiceInterface\Runtime\RuntimeTelemetrySinkConfig;
use App\ServiceInterface\Runtime\RuntimeTelemetrySnapshot;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!function_exists('frankenphp_handle_request')) {
    fwrite(STDERR, "frankenphp_handle_request() is not available. Are you running under FrankenPHP worker mode?\n");
    exit(1);
}

// Prevent worker script termination when a client connection is interrupted
ignore_user_abort(true);

final class RuntimeFrankenphpEnv
{
    public static function str(string $key, string $default): string
    {
        $v = getenv($key);
        if (!is_string($v) || $v === '') {
            return $default;
        }
        return $v;
    }

    public static function int(string $key, int $default): int
    {
        $v = getenv($key);
        if (!is_string($v) || $v === '') {
            return $default;
        }
        if (!is_numeric($v)) {
            return $default;
        }
        return (int) $v;
    }

    public static function bool(string $key, bool $default): bool
    {
        $v = getenv($key);
        if (!is_string($v) || $v === '') {
            return $default;
        }
        $v = strtolower(trim($v));
        if (in_array($v, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($v, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }
        return $default;
    }
}

final class RuntimeFrankenphpRecycle
{
    public int $maxRequest;
    public int $maxUptimeSec;
    public int $maxMemoryMb;

    private int $request = 0;
    private float $startAt;

    public function __construct(int $maxRequest, int $maxUptimeSec, int $maxMemoryMb)
    {
        $this->maxRequest = max(0, $maxRequest);
        $this->maxUptimeSec = max(30, $maxUptimeSec);
        $this->maxMemoryMb = max(64, $maxMemoryMb);
        $this->startAt = microtime(true);
    }

    public function tick(): void
    {
        $this->request++;
    }

    public function uptime(): float
    {
        return max(0.0, microtime(true) - $this->startAt);
    }

    public function startAt(): float
    {
        return $this->startAt;
    }

    public function shouldRecycle(string &$reason): bool
    {
        if ($this->maxRequest > 0 && $this->request >= $this->maxRequest) {
            $reason = 'maxRequest';
            return true;
        }

        if ($this->uptime() >= (float) $this->maxUptimeSec) {
            $reason = 'maxUptime';
            return true;
        }

        $mb = (int) floor(memory_get_usage(true) / 1024 / 1024);
        if ($mb >= $this->maxMemoryMb) {
            $reason = 'maxMemory';
            return true;
        }

        $reason = 'ok';
        return false;
    }
}

/**
 * @param array<string,string> $label
 */
function runtimeMetricKey(string $name, array $label): string
{
    if ($label === []) {
        return $name;
    }

    $pair = [];
    foreach ($label as $k => $v) {
        $pair[] = $k . '=' . $v;
    }

    return $name . '{' . implode(',', $pair) . '}';
}

$appEnv = RuntimeFrankenphpEnv::str('APP_ENV', 'prod');
$appDebug = RuntimeFrankenphpEnv::bool('APP_DEBUG', false);

if (RuntimeFrankenphpEnv::str('RUNTIME_ENGINE', '') === '') {
    putenv('RUNTIME_ENGINE=frankenphp');
}

$workerId = RuntimeFrankenphpEnv::str('RUNTIME_WORKER_ID', 'pid-' . (string) getmypid());
putenv('RUNTIME_WORKER_ID=' . $workerId);

$telemetryDir = RuntimeFrankenphpEnv::str('RUNTIME_TELEMETRY_DIR', 'var/runtime/telemetry');

$maxRequest = RuntimeFrankenphpEnv::int('RUNTIME_FRANKENPHP_MAX_REQUEST', 0);
$maxUptime = RuntimeFrankenphpEnv::int('RUNTIME_FRANKENPHP_MAX_UPTIME_SEC', 1800);
$maxMemMb = RuntimeFrankenphpEnv::int('RUNTIME_FRANKENPHP_MAX_MEMORY_MB', 512);

$recycle = new RuntimeFrankenphpRecycle($maxRequest, $maxUptime, $maxMemMb);

$kernel = new Kernel($appEnv, $appDebug);
$kernel->boot();

$telemetry = new RuntimeTelemetrySnapshot('runtime');
$codec = new RuntimeTelemetryJsonCodec();
$sinkCfg = new RuntimeTelemetrySinkConfig($telemetryDir, $workerId, 1.0);
$sink = new RuntimeTelemetryFileSink($sinkCfg, $codec);

$memoryHighWater = 0.0;

$handler = static function () use ($kernel, $telemetry, &$memoryHighWater, $recycle, $sink): void {
    $t0 = microtime(true);

    try {
        $request = Request::createFromGlobals();
    } catch (\Throwable $e) {
        $request = Request::create('/error', 'GET');
    }

    try {
        $response = $kernel->handle($request);
        if (!$response instanceof Response) {
            $response = new Response('', 500);
        }
    } catch (\Throwable $e) {
        $response = new Response('Internal Server Error', 500);
    }

    $dt = max(0.0, microtime(true) - $t0);
    $status = (int) $response->getStatusCode();

    $keyRequest = runtimeMetricKey('runtime_supercharger_request_total', [
        'engine' => 'frankenphp',
        'status' => (string) $status,
    ]);
    $telemetry->counter[$keyRequest] = ($telemetry->counter[$keyRequest] ?? 0) + 1;

    $keyCount = runtimeMetricKey('runtime_supercharger_request_duration_count', [
        'engine' => 'frankenphp',
    ]);
    $telemetry->gauge[$keyCount] = (float) (($telemetry->gauge[$keyCount] ?? 0.0) + 1.0);

    $keySum = runtimeMetricKey('runtime_supercharger_request_duration_sum', [
        'engine' => 'frankenphp',
    ]);
    $telemetry->gauge[$keySum] = (float) (($telemetry->gauge[$keySum] ?? 0.0) + $dt);

    $keyMax = runtimeMetricKey('runtime_supercharger_request_duration_max', [
        'engine' => 'frankenphp',
    ]);
    $telemetry->gauge[$keyMax] = max((float) ($telemetry->gauge[$keyMax] ?? 0.0), $dt);

    $telemetry->gauge['runtime_supercharger_worker_start_time_second'] = $recycle->startAt();
    $telemetry->gauge['runtime_supercharger_worker_uptime_second'] = $recycle->uptime();

    $mem = (float) memory_get_usage(true);
    $memoryHighWater = max($memoryHighWater, $mem);
    $telemetry->gauge['runtime_supercharger_memory_high_water_byte'] = $memoryHighWater;

    try {
        $response->send();
    } catch (\Throwable $e) {
        // ignore send failures
    }

    try {
        $kernel->terminate($request, $response);
    } catch (\Throwable $e) {
        // ignore terminate failures
    }

    // Flush per-request telemetry snapshot
    $sink->flush($telemetry);

    // Encourage GC at request boundary
    gc_collect_cycles();
};

$maxLoop = $maxRequest > 0 ? $maxRequest : 0;

for ($nbRequests = 0; !$maxLoop || $nbRequests < $maxLoop; ++$nbRequests) {
    $keepRunning = \frankenphp_handle_request($handler);

    if (!$keepRunning) {
        break;
    }

    $recycle->tick();

    $reason = 'ok';
    if ($recycle->shouldRecycle($reason)) {
        $keyRecycle = runtimeMetricKey('runtime_supercharger_recycle_total', [
            'engine' => 'frankenphp',
            'action' => 'exit',
            'reason' => $reason,
        ]);
        $telemetry->counter[$keyRecycle] = ($telemetry->counter[$keyRecycle] ?? 0) + 1;
        $sink->flush($telemetry);
        break;
    }
}

try {
    $kernel->shutdown();
} catch (\Throwable $e) {
    // ignore
}

exit(0);
