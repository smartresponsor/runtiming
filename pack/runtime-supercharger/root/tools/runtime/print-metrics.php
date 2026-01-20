<?php
declare(strict_types=1);



require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeTelemetrySnapshot.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeTelemetryInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimePrometheusExporterInterface.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeTelemetry.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimePrometheusExporter.php';

use App\Service\Runtime\RuntimePrometheusExporter;
use App\Service\Runtime\RuntimeTelemetry;

$now = 0.0;
$mem = 10;

$telemetry = new RuntimeTelemetry('runtime', function () use (&$now): float { return $now; }, function () use (&$mem): int { return $mem; });

// Simulate a few requests.
for ($i = 0; $i < 3; $i++) {
    $telemetry->beforeRequest('sim');
    $now += 0.123;
    $mem += 50;
    $telemetry->afterRequest('sim', 200, $i === 2, 'gracefulExit', $i === 2 ? 'maxRequest' : 'none');
    $now += 0.010;
}

$exporter = new RuntimePrometheusExporter();
echo $exporter->export($telemetry->snapshot(), true);
