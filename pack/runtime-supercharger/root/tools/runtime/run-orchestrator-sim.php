<?php
declare(strict_types=1);



require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeResetInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeResetRegistryInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeKernelResetInterface.php';

require_once __DIR__ . '/../../src/Service/Runtime/RuntimeResetReport.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeResetRegistry.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeKernelResetAdapter.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeSymfonyResetReport.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeSymfonyResetOrchestrator.php';

require_once __DIR__ . '/fixture/FixtureCounterResetter.php';
require_once __DIR__ . '/fixture/FixtureKernelResetInner.php';

use App\Service\Runtime\RuntimeKernelResetAdapter;
use App\Service\Runtime\RuntimeResetRegistry;
use App\Service\Runtime\RuntimeSymfonyResetOrchestrator;

$registry = new RuntimeResetRegistry(true);
$counter = new FixtureCounterResetter();
$registry->add($counter);

$inner = new FixtureKernelResetInner();
$kernelReset = new RuntimeKernelResetAdapter($inner);

$orchestrator = new RuntimeSymfonyResetOrchestrator(
    resetRegistry: $registry,
    kernelReset: $kernelReset,
    gcCollect: true,
);

$report = $orchestrator->afterResponse();

fwrite(STDOUT, json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
