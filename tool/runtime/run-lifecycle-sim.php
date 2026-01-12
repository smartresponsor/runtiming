<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RunnerResponse.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeLifecycleConfig.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeLifecycleState.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeLifecycleDecision.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeLifecyclePolicyInterface.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeLifecyclePolicy.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeLifecycleHeaderInjector.php';

use App\ServiceInterface\Runtime\RunnerResponse;
use App\ServiceInterface\Runtime\RuntimeLifecycleConfig;
use App\Service\Runtime\RuntimeLifecyclePolicy;
use App\Service\Runtime\RuntimeLifecycleHeaderInjector;

$time = 0.0;
$mem = 10;

$now = function () use (&$time): float { return $time; };
$memory = function () use (&$mem): int { return $mem; };
$rand = function (int $min, int $max): int { return $min; }; // deterministic

$cfg = new RuntimeLifecycleConfig(
    maxRequest: 3,
    jitterPercent: 0.0,
    maxMemoryByte: 100,
    emergencyMemoryByte: 150,
    maxUptimeSec: 10.0,
    maxIdleSec: 5.0,
    maxRequestDurationSec: 2.0,
    maxMemoryGrowthByte: 50
);

$policy = new RuntimeLifecyclePolicy($cfg, $now, $memory, $rand);
$inject = new RuntimeLifecycleHeaderInjector();

$out = [];

for ($i = 1; $i <= 4; $i++) {
    $policy->beforeRequest();
    $time += 1.1; // duration
    $mem += 30;   // memory growth
    $dec = $policy->afterRequest();

    $res = new RunnerResponse(200, [], 'ok');
    $res2 = $inject->apply($res, $dec);

    $out[] = [
        'i' => $i,
        'decision' => ['recycle' => $dec->recycle, 'action' => $dec->action, 'reason' => $dec->reason],
        'header' => $res2->getHeader(),
    ];

    $time += 0.2; // idle gap
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
