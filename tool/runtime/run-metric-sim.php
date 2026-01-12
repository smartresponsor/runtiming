<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeMetricRegistryInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeMetricExporterInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeSuperchargerMetricEmitterInterface.php';

require_once __DIR__ . '/../../src/Service/Runtime/RuntimePrometheusFormatter.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimePrometheusRegistry.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimePrometheusFileExporter.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeSuperchargerMetricEmitter.php';

use App\Service\Runtime\RuntimePrometheusRegistry;
use App\Service\Runtime\RuntimePrometheusFileExporter;
use App\Service\Runtime\RuntimeSuperchargerMetricEmitter;

final class FakeDecision
{
    public function toArray(): array { return ['shouldRecycle' => true, 'reason' => 'maxRequest']; }
}

final class FakeReport
{
    public function toArray(): array { return ['count' => 7]; }
}

$registry = new RuntimePrometheusRegistry();
$emitter = new RuntimeSuperchargerMetricEmitter($registry);

$emitter->onReset(new FakeReport(), 0.013, true);
$emitter->onWorkerDecision(new FakeDecision());
$emitter->onSnapshot(123, 12, 42);

$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'runtime-supercharger.prom';
$exporter = new RuntimePrometheusFileExporter($path);
$exporter->export($registry);

fwrite(STDOUT, "written: " . $path . "\n");
fwrite(STDOUT, $registry->renderText());
