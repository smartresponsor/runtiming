<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeStatusSnapshot
{
    public string $engine;
    public string $workerId;
    public string $php;
    public float $workerStartTime;
    public float $workerUptime;
    public int $memoryUsageByte;
    public int $memoryUsageRealByte;
    public int $memoryPeakByte;
    public int $memoryPeakRealByte;

    /** @var array<string,mixed> */
    public array $extra = [];

    public function __construct()
    {
        $this->engine = 'unknown';
        $this->workerId = 'unknown';
        $this->php = PHP_VERSION;

        $this->workerStartTime = 0.0;
        $this->workerUptime = 0.0;

        $this->memoryUsageByte = 0;
        $this->memoryUsageRealByte = 0;
        $this->memoryPeakByte = 0;
        $this->memoryPeakRealByte = 0;
    }
}
