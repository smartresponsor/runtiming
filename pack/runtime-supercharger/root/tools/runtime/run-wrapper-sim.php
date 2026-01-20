<?php
declare(strict_types=1);



require_once __DIR__ . '/../src/ServiceInterface/Runtime/RunnerRequest.php';
require_once __DIR__ . '/../src/ServiceInterface/Runtime/RunnerResponse.php';
require_once __DIR__ . '/../src/ServiceInterface/Runtime/RuntimeRunnerInterface.php';

require_once __DIR__ . '/../src/ServiceInterface/Runtime/RuntimeEngineAdapterInterface.php';
require_once __DIR__ . '/../src/ServiceInterface/Runtime/RuntimeEngineAction.php';
require_once __DIR__ . '/../src/Service/Runtime/RuntimeEngineAdapter.php';

require_once __DIR__ . '/../src/Service/Runtime/RuntimeFakeRunner.php';

use App\Service\Runtime\RuntimeEngineAdapter;
use App\Service\Runtime\RuntimeFakeRunner;
use App\ServiceInterface\Runtime\RunnerRequest;

$adapter = new RuntimeEngineAdapter(false);
$runner = new RuntimeFakeRunner();
$runner->boot();

$paths = ['/a', '/b', '/c'];
$out = [];

foreach ($paths as $p) {
    $res = $runner->handle(new RunnerRequest('GET', $p));
    $runner->terminate(new RunnerRequest('GET', $p), $res);

    $action = $adapter->plan(null, $res->getHeader());

    $out[] = [
        'path' => $p,
        'header' => $res->getHeader(),
        'action' => $action->toArray(),
    ];
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
