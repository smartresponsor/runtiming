<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeTelemetrySinkConfig
{
    public string $dir;
    public string $workerId;
    public float $flushIntervalSec;

    public function __construct(string $dir, string $workerId, float $flushIntervalSec = 0.0)
    {
        $this->dir = $dir !== '' ? $dir : 'var/runtime/telemetry';
        $this->workerId = $workerId !== '' ? $workerId : ('pid-' . (string) getmypid());
        $this->flushIntervalSec = max(0.0, $flushIntervalSec);
    }
}
