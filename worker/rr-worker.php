<?php
declare(strict_types=1);



use App\Kernel;
use App\Service\Runtime\RuntimeTelemetryFileSink;
use App\Service\Runtime\RuntimeTelemetryJsonCodec;
use App\ServiceInterface\Runtime\RuntimeTelemetrySinkConfig;
use App\ServiceInterface\Runtime\RuntimeTelemetrySnapshot;
use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response as SfResponse;

require_once dirname(__DIR__) . '/vendor/autoload.php';

final class RuntimeRoadRunnerEnv
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

final class RuntimeRoadRunnerRecycle
{
    public int $maxRequest;
    public int $maxUptimeSec;
    public int $maxMemoryMb;

    private int $request = 0;
    private float $startAt;

    public function __construct(int $maxRequest, int $maxUptimeSec, int $maxMemoryMb)
    {
        $this->maxRequest = max(1, $maxRequest);
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
        if ($this->request >= $this->maxRequest) {
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

$appEnv = RuntimeRoadRunnerEnv::str('APP_ENV', 'prod');
$appDebug = RuntimeRoadRunnerEnv::bool('APP_DEBUG', false);

putenv('RUNTIME_ENGINE=rr');

$workerId = RuntimeRoadRunnerEnv::str('RUNTIME_WORKER_ID', 'pid-' . (string) getmypid());
putenv('RUNTIME_WORKER_ID=' . $workerId);

$telemetryDir = RuntimeRoadRunnerEnv::str('RUNTIME_TELEMETRY_DIR', 'var/runtime/telemetry');

$maxRequest = RuntimeRoadRunnerEnv::int('RUNTIME_RR_MAX_REQUEST', 5000);
$maxUptime = RuntimeRoadRunnerEnv::int('RUNTIME_RR_MAX_UPTIME_SEC', 1800);
$maxMemMb = RuntimeRoadRunnerEnv::int('RUNTIME_RR_MAX_MEMORY_MB', 512);

$recycle = new RuntimeRoadRunnerRecycle($maxRequest, $maxUptime, $maxMemMb);

$psr17 = new Psr17Factory();
$httpFoundationFactory = new HttpFoundationFactory();
$psrHttpFactory = new PsrHttpFactory($psr17, $psr17, $psr17, $psr17);

$rrWorker = Worker::create();
$psr7 = new PSR7Worker($rrWorker, $psr17, $psr17, $psr17);

$kernel = new Kernel($appEnv, $appDebug);
$kernel->boot();

$telemetry = new RuntimeTelemetrySnapshot('runtime');
$codec = new RuntimeTelemetryJsonCodec();
$sinkCfg = new RuntimeTelemetrySinkConfig($telemetryDir, $workerId, 1.0);
$sink = new RuntimeTelemetryFileSink($sinkCfg, $codec);

$memoryHighWater = 0.0;

while (true) {
    try {
        $req = $psr7->waitRequest();
        if ($req === null) {
            break;
        }
    } catch (Throwable $e) {
        // Protocol/transport error - keep worker alive but expose the failure via recycle counter.
        $telemetry->counter[runtimeMetricKey('runtime_supercharger_recycle_total', [
            'engine' => 'rr',
            'action' => 'continue',
            'reason' => 'waitError',
        ])] = ($telemetry->counter[runtimeMetricKey('runtime_supercharger_recycle_total', [
            'engine' => 'rr',
            'action' => 'continue',
            'reason' => 'waitError',
        ])] ?? 0) + 1;

        $sink->flush($telemetry);
        continue;
    }

    $t0 = microtime(true);
    $sfReq = $httpFoundationFactory->createRequest($req);

    try {
        $sfRes = $kernel->handle($sfReq);
        if (!$sfRes instanceof SfResponse) {
            $sfRes = new SfResponse('', 500);
        }
    } catch (Throwable $e) {
        $sfRes = new SfResponse('Internal Server Error', 500);
    }

    $dt = max(0.0, microtime(true) - $t0);
    $status = (int) $sfRes->getStatusCode();

    $telemetry->counter[runtimeMetricKey('runtime_supercharger_request_total', [
        'engine' => 'rr',
        'status' => (string) $status,
    ])] = ($telemetry->counter[runtimeMetricKey('runtime_supercharger_request_total', [
        'engine' => 'rr',
        'status' => (string) $status,
    ])] ?? 0) + 1;

    $telemetry->gauge[runtimeMetricKey('runtime_supercharger_request_duration_count', [
        'engine' => 'rr',
    ])] = (float) (($telemetry->gauge[runtimeMetricKey('runtime_supercharger_request_duration_count', [
        'engine' => 'rr',
    ])] ?? 0.0) + 1.0);

    $telemetry->gauge[runtimeMetricKey('runtime_supercharger_request_duration_sum', [
        'engine' => 'rr',
    ])] = (float) (($telemetry->gauge[runtimeMetricKey('runtime_supercharger_request_duration_sum', [
        'engine' => 'rr',
    ])] ?? 0.0) + $dt);

    $kMax = runtimeMetricKey('runtime_supercharger_request_duration_max', ['engine' => 'rr']);
    $telemetry->gauge[$kMax] = max((float) ($telemetry->gauge[$kMax] ?? 0.0), $dt);

    $telemetry->gauge['runtime_supercharger_worker_start_time_second'] = $recycle->startAt();
    $telemetry->gauge['runtime_supercharger_worker_uptime_second'] = $recycle->uptime();

    $mem = (float) memory_get_usage(true);
    $memoryHighWater = max($memoryHighWater, $mem);
    $telemetry->gauge['runtime_supercharger_memory_high_water_byte'] = $memoryHighWater;

    try {
        $psrRes = $psrHttpFactory->createResponse($sfRes);
        $psr7->respond($psrRes);
    } catch (Throwable $e) {
        // Best effort: do not kill worker for a response conversion error.
    }

    try {
        $kernel->terminate($sfReq, $sfRes);
    } catch (Throwable $e) {
        // ignore
    }

    $recycle->tick();

    $reason = 'ok';
    if ($recycle->shouldRecycle($reason)) {
        $telemetry->counter[runtimeMetricKey('runtime_supercharger_recycle_total', [
            'engine' => 'rr',
            'action' => 'exit',
            'reason' => $reason,
        ])] = ($telemetry->counter[runtimeMetricKey('runtime_supercharger_recycle_total', [
            'engine' => 'rr',
            'action' => 'exit',
            'reason' => $reason,
        ])] ?? 0) + 1;

        $sink->flush($telemetry);
        break;
    }

    $sink->flush($telemetry);
}

try {
    $kernel->shutdown();
} catch (Throwable $e) {
    // ignore
}

exit(0);
