<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeWorkerDecision
{
    private bool $shouldRecycle;
    private string $reason;
    private int $requestCount;
    private int $uptimeSec;
    private int $rssMemoryMb;
    private int $statusCode;

    public function __construct(
        bool $shouldRecycle,
        string $reason,
        int $requestCount,
        int $uptimeSec,
        int $rssMemoryMb,
        int $statusCode
    ) {
        $this->shouldRecycle = $shouldRecycle;
        $this->reason = $reason;
        $this->requestCount = $requestCount;
        $this->uptimeSec = $uptimeSec;
        $this->rssMemoryMb = $rssMemoryMb;
        $this->statusCode = $statusCode;
    }

    public function getShouldRecycle(): bool
    {
        return $this->shouldRecycle;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }

    public function getUptimeSec(): int
    {
        return $this->uptimeSec;
    }

    public function getRssMemoryMb(): int
    {
        return $this->rssMemoryMb;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'shouldRecycle' => $this->shouldRecycle,
            'reason' => $this->reason,
            'requestCount' => $this->requestCount,
            'uptimeSec' => $this->uptimeSec,
            'rssMemoryMb' => $this->rssMemoryMb,
            'statusCode' => $this->statusCode,
        ];
    }
}
