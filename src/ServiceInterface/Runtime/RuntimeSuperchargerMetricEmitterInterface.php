<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeSuperchargerMetricEmitterInterface
{
    public function onReset(object $report, float $durationSec, bool $ok): void;

    public function onWorkerDecision(object $decision): void;

    public function onSnapshot(int $rssMemoryMb, int $uptimeSec, int $requestCount): void;
}
