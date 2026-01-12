<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerStatInterface;

final class RuntimeWorkerStat implements RuntimeWorkerStatInterface
{
    private int $requestCount = 0;

    /** @var int|float */
    private $startSec;

    public function __construct()
    {
        $this->startSec = microtime(true);
    }

    public function getUptimeSec(): int
    {
        $now = microtime(true);
        $diff = $now - $this->startSec;
        return (int) max(0, floor($diff));
    }

    public function getRssMemoryMb(): int
    {
        $mb = RuntimeWorkerRssMemory::readRssMb();
        return max(0, $mb);
    }

    public function incRequest(): int
    {
        $this->requestCount++;
        return $this->requestCount;
    }

    public function reset(): void
    {
        $this->requestCount = 0;
        $this->startSec = microtime(true);
    }
}
