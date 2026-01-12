<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeTelemetrySnapshot.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimePrometheusExporterInterface.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimePrometheusExporter.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeTelemetryJsonCodec.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeTelemetryAggregate.php';

use App\Service\Runtime\RuntimePrometheusExporter;
use App\Service\Runtime\RuntimeTelemetryAggregate;
use App\Service\Runtime\RuntimeTelemetryJsonCodec;

$dir = (string) ($argv[1] ?? getenv('RUNTIME_TELEMETRY_DIR') ?: 'var/runtime/telemetry');

$codec = new RuntimeTelemetryJsonCodec();
$agg = new RuntimeTelemetryAggregate($dir, $codec);
$snap = $agg->aggregate();

$exporter = new RuntimePrometheusExporter();
echo $exporter->export($snap, true);
