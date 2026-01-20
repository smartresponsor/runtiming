<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';


use App\Service\Runtime\RuntimePrometheusExporter;
use App\Service\Runtime\RuntimeTelemetryAggregate;
use App\Service\Runtime\RuntimeTelemetryJsonCodec;

$dir = (string) ($argv[1] ?? getenv('RUNTIME_TELEMETRY_DIR') ?: 'var/runtime/telemetry');

$codec = new RuntimeTelemetryJsonCodec();
$agg = new RuntimeTelemetryAggregate($dir, $codec);
$snap = $agg->aggregate();

$exporter = new RuntimePrometheusExporter();
echo $exporter->export($snap, true);
