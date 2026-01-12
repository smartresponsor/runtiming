<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeLifecycleConfig
{
    public int $maxRequest;
    public float $jitterPercent;
    public int $maxMemoryByte;
    public int $emergencyMemoryByte;
    public float $maxUptimeSec;
    public float $maxIdleSec;
    public float $maxRequestDurationSec;
    public int $maxMemoryGrowthByte;

    public function __construct(
        int $maxRequest = 0,
        float $jitterPercent = 0.0,
        int $maxMemoryByte = 0,
        int $emergencyMemoryByte = 0,
        float $maxUptimeSec = 0.0,
        float $maxIdleSec = 0.0,
        float $maxRequestDurationSec = 0.0,
        int $maxMemoryGrowthByte = 0
    ) {
        $this->maxRequest = $maxRequest;
        $this->jitterPercent = max(0.0, $jitterPercent);
        $this->maxMemoryByte = $maxMemoryByte;
        $this->emergencyMemoryByte = $emergencyMemoryByte;
        $this->maxUptimeSec = $maxUptimeSec;
        $this->maxIdleSec = $maxIdleSec;
        $this->maxRequestDurationSec = $maxRequestDurationSec;
        $this->maxMemoryGrowthByte = $maxMemoryGrowthByte;
    }
}
