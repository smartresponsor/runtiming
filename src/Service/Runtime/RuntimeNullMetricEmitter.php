<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerMetricEmitterInterface;

final class RuntimeNullMetricEmitter implements RuntimeSuperchargerMetricEmitterInterface
{
    public function onReset(object $report, float $durationSec, bool $ok): void
    {
        // no-op
    }

    public function onWorkerDecision(object $decision): void
    {
        // no-op
    }

    public function onSnapshot(int $rssMemoryMb, int $uptimeSec, int $requestCount): void
    {
        // no-op
    }
}
