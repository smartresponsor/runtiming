<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';


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
