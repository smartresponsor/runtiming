<?php
declare(strict_types=1);



require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RunnerRequest.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RunnerResponse.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeRunnerInterface.php';

require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeEngineAdapterInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeEngineAction.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeEngineAdapter.php';

require_once __DIR__ . '/../../src/Service/Runtime/RuntimeFakeRunner.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerLoop.php';

use App\Service\Runtime\RuntimeEngineAdapter;
use App\Service\Runtime\RuntimeFakeRunner;
use App\Service\Runtime\RuntimeWorkerLoop;
use App\ServiceInterface\Runtime\RunnerRequest;
use App\ServiceInterface\Runtime\RunnerResponse;

$adapter = new RuntimeEngineAdapter(false);
$runner = new RuntimeFakeRunner();

$loop = new RuntimeWorkerLoop($runner, $adapter, 0);

$stdin = fopen('php://stdin', 'r');
if (!$stdin) {
    fwrite(STDERR, "stdin not available\n");
    exit(2);
}

fwrite(STDOUT, "runtime-supercharger-wrapper-cli: enter PATH lines, or 'quit'\n");

$nextRequest = function () use ($stdin): RunnerRequest {
    while (true) {
        $line = fgets($stdin);
        if ($line === false) {
            // EOF -> exit worker
            exit(0);
        }
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if ($line === 'quit') {
            exit(0);
        }
        return new RunnerRequest('GET', $line, [], '');
    }
};

$emitResponse = function (RunnerResponse $res): void {
    $header = $res->getHeader();
    fwrite(STDOUT, "status={$res->getStatusCode()} body=" . $res->getBody() . "\n");
    foreach ($header as $k => $v) {
        fwrite(STDOUT, "header:$k=$v\n");
    }
};

$loop->run($nextRequest, $emitResponse);
