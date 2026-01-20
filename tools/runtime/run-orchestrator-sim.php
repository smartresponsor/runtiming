<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';



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
