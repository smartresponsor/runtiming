<?php
declare(strict_types=1);



require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerLimitInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerSupervisorInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerEventSinkInterface.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerLimit.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerDecision.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeMemory.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerEventFileSink.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerSupervisor.php';

use App\Service\Runtime\RuntimeWorkerEventFileSink;
use App\Service\Runtime\RuntimeWorkerLimit;
use App\Service\Runtime\RuntimeWorkerSupervisor;

$root = dirname(__DIR__, 2);
$eventPath = $root . '/report/runtime/runtime-worker-limit-event.ndjson';

@unlink($eventPath);

$limit = RuntimeWorkerLimit::default();
$sink = new RuntimeWorkerEventFileSink($eventPath);
$supervisor = new RuntimeWorkerSupervisor($limit, $sink);

$served = 0;
$recycle = false;
$reason = 'ok';

for ($i = 0; $i < 1200; $i++) {
    $served++;
    $d = $supervisor->afterRequest(200);

    if ($d->shouldRecycle()) {
        $recycle = true;
        $reason = $d->getReason();
        break;
    }
}

$out = [
    'served' => $served,
    'recycle' => $recycle,
    'reason' => $reason,
    'limit' => $limit->toArray(),
    'eventPath' => 'report/runtime/runtime-worker-limit-event.ndjson',
];

fwrite(STDOUT, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
