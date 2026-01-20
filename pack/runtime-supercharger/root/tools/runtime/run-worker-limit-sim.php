<?php
declare(strict_types=1);



require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerDecision.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerLimitInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerStatInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerSupervisorInterface.php';

require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerLimit.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerStat.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerRssMemory.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerSupervisor.php';

use App\Service\Runtime\RuntimeWorkerLimit;
use App\Service\Runtime\RuntimeWorkerStat;
use App\Service\Runtime\RuntimeWorkerSupervisor;

$limit = new RuntimeWorkerLimit(3, 10_000, 10_000, 0);
$stat = new RuntimeWorkerStat();
$supervisor = new RuntimeWorkerSupervisor($limit, $stat);

$dec = [];
for ($i = 0; $i < 4; $i++) {
    $d = $supervisor->afterRequest(200);
    $dec[] = $d->toArray();
    if ($d->getShouldRecycle()) {
        break;
    }
}

$out = [
    'decision' => $dec,
    'note' => 'Recycle expected on requestCount >= 3 in this sim.',
];

fwrite(STDOUT, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
