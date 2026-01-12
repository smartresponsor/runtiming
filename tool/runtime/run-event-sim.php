<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeEventSinkInterface.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeEventFileSink.php';

require_once __DIR__ . '/fixture/FixtureResetReport.php';
require_once __DIR__ . '/fixture/FixtureWorkerDecision.php';
require_once __DIR__ . '/fixture/FixtureResetRegistryInterface.php';
require_once __DIR__ . '/fixture/FixtureWorkerSupervisorInterface.php';
require_once __DIR__ . '/fixture/FixtureResetRegistry.php';
require_once __DIR__ . '/fixture/FixtureWorkerSupervisor.php';

require_once __DIR__ . '/ToolResetRegistryEventDecorator.php';
require_once __DIR__ . '/ToolWorkerSupervisorEventDecorator.php';

use App\Service\Runtime\RuntimeEventFileSink;
use App\Tool\Runtime\ToolResetRegistryEventDecorator;
use App\Tool\Runtime\ToolWorkerSupervisorEventDecorator;
use App\Tool\Runtime\Fixture\FixtureResetRegistry;
use App\Tool\Runtime\Fixture\FixtureWorkerSupervisor;

$root = dirname(__DIR__, 2);
$path = $root . '/report/runtime/runtime-supercharger-event.ndjson';
@unlink($path);

$sink = new RuntimeEventFileSink($path, 2);

$reset = new ToolResetRegistryEventDecorator(new FixtureResetRegistry(), $sink);
$worker = new ToolWorkerSupervisorEventDecorator(new FixtureWorkerSupervisor(), $sink);

for ($i = 0; $i < 3; $i++) {
    $reset->resetAll();
    $d = $worker->afterRequest(200);
    if ($d->toArray()['shouldRecycle'] === true) {
        break;
    }
}

$out = [
    'eventPath' => 'report/runtime/runtime-supercharger-event.ndjson',
    'ok' => is_file($path),
];

fwrite(STDOUT, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
