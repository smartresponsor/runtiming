<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerLimitInterface;

final class RuntimeWorkerLimit implements RuntimeWorkerLimitInterface
{
    private int $maxRequest;
    private int $maxUptimeSec;
    private int $maxMemoryMb;
    private int $softMemoryMb;

    public function __construct(int $maxRequest, int $maxUptimeSec, int $maxMemoryMb, int $softMemoryMb)
    {
        $this->maxRequest = max(1, $maxRequest);
        $this->maxUptimeSec = max(1, $maxUptimeSec);
        $this->maxMemoryMb = max(1, $maxMemoryMb);
        $this->softMemoryMb = max(0, $softMemoryMb);
    }

    public static function default(): self
    {
        return new self(3000, 900, 384, 256);
    }

    public function getMaxRequest(): int
    {
        return $this->maxRequest;
    }

    public function getMaxUptimeSec(): int
    {
        return $this->maxUptimeSec;
    }

    public function getMaxMemoryMb(): int
    {
        return $this->maxMemoryMb;
    }

    public function getSoftMemoryMb(): int
    {
        return $this->softMemoryMb;
    }
}
