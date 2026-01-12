<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeLifecycleState
{
    public float $startAt = 0.0;
    public float $lastAt = 0.0;
    public int $requestCount = 0;

    public int $baselineMemoryByte = 0;
    public int $memoryHighWaterByte = 0;

    public int $effectiveMaxRequest = 0;
    public float $effectiveMaxUptimeSec = 0.0;
    public float $effectiveMaxIdleSec = 0.0;

    public float $lastIdleSec = 0.0;
    public float $lastDurationSec = 0.0;
    public int $lastMemoryByte = 0;
}
