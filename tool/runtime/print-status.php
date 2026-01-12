<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeStatusSnapshot.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeStatusProviderInterface.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeStatusProvider.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeTelemetryDirInspector.php';

use App\Service\Runtime\RuntimeStatusProvider;
use App\Service\Runtime\RuntimeTelemetryDirInspector;

$dir = (string) ($argv[1] ?? getenv('RUNTIME_TELEMETRY_DIR') ?: 'var/runtime/telemetry');

$provider = new RuntimeStatusProvider($dir);
$inspector = new RuntimeTelemetryDirInspector($dir);

$s = $provider->snapshot();
$i = $inspector->inspect();

echo json_encode([
    'worker' => [
        'engine' => $s->engine,
        'workerId' => $s->workerId,
        'php' => $s->php,
        'workerStartTime' => $s->workerStartTime,
        'workerUptime' => $s->workerUptime,
        'memoryUsageByte' => $s->memoryUsageByte,
        'memoryUsageRealByte' => $s->memoryUsageRealByte,
        'memoryPeakByte' => $s->memoryPeakByte,
        'memoryPeakRealByte' => $s->memoryPeakRealByte,
        'extra' => $s->extra,
    ],
    'host' => [
        'telemetry' => $i,
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
