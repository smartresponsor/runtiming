<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeStatusProviderInterface;
use App\ServiceInterface\Runtime\RuntimeStatusSnapshot;

final class RuntimeStatusProvider implements RuntimeStatusProviderInterface
{
    private string $telemetryDir;

    /** @var callable():float */
    private $now;

    private float $workerStartAt;

    /**
     * @param callable():float|null $now
     */
    public function __construct(string $telemetryDir = 'var/runtime/telemetry', ?callable $now = null)
    {
        $this->telemetryDir = $telemetryDir !== '' ? $telemetryDir : 'var/runtime/telemetry';
        $this->now = $now ?? static fn (): float => microtime(true);
        $this->workerStartAt = ($this->now)();
    }

    public function snapshot(): RuntimeStatusSnapshot
    {
        $snap = new RuntimeStatusSnapshot();

        $snap->engine = $this->env('RUNTIME_ENGINE', 'unknown');
        $snap->workerId = $this->env('RUNTIME_WORKER_ID', 'pid-' . (string) getmypid());

        $now = ($this->now)();
        $snap->workerStartTime = $this->workerStartAt;
        $snap->workerUptime = max(0.0, $now - $this->workerStartAt);

        $snap->memoryUsageByte = (int) memory_get_usage(false);
        $snap->memoryUsageRealByte = (int) memory_get_usage(true);
        $snap->memoryPeakByte = (int) memory_get_peak_usage(false);
        $snap->memoryPeakRealByte = (int) memory_get_peak_usage(true);

        $snap->extra = [
            'telemetryDir' => $this->telemetryDir,
            'sapi' => PHP_SAPI,
            'timeUnix' => (int) $now,
        ];

        return $snap;
    }

    private function env(string $key, string $default): string
    {
        $v = getenv($key);
        if (!is_string($v) || $v === '') {
            return $default;
        }
        return $v;
    }
}
