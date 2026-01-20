<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerStateInterface;

final class RuntimeWorkerState implements RuntimeWorkerStateInterface
{
    private float $startAt;
    private int $requestCount;
    private bool $recyclePending;
    private string $recycleReason;

    private int $drainSecond;
    private float $drainDeadlineAt;

    private bool $signalInstalled;

    public function __construct(string $drainSecond)
    {
        $this->startAt = microtime(true);
        $this->requestCount = 0;
        $this->recyclePending = false;
        $this->recycleReason = '';

        $this->drainSecond = max(0, (int) trim($drainSecond));
        $this->drainDeadlineAt = 0.0;

        $this->signalInstalled = false;
    }

    public function onRequestStart(): void
    {
        $this->requestCount++;

        // Signal handling is optional and only available in CLI with pcntl.
        if (!$this->signalInstalled) {
            $this->installSignal();
        }

        if (function_exists('pcntl_signal_dispatch')) {
            @pcntl_signal_dispatch();
        }
    }

    public function getStartAtFloat(): float
    {
        return $this->startAt;
    }

    public function getRequestCount(): int
    {
        return $this->requestCount;
    }

    public function getUptimeSecond(): int
    {
        $d = microtime(true) - $this->startAt;
        if ($d < 0) {
            $d = 0;
        }
        return (int) floor($d);
    }

    public function getMemoryUsageMb(): int
    {
        $b = (int) memory_get_usage(true);
        return (int) floor($b / 1024 / 1024);
    }

    public function getMemoryPeakMb(): int
    {
        $b = (int) memory_get_peak_usage(true);
        return (int) floor($b / 1024 / 1024);
    }

    public function markRecycle(string $reason): void
    {
        if ($this->recyclePending) {
            return;
        }

        $this->recyclePending = true;
        $this->recycleReason = $reason !== '' ? $reason : 'pending';

        if ($this->drainSecond > 0) {
            $this->drainDeadlineAt = microtime(true) + (float) $this->drainSecond;
        }
    }

    public function isRecyclePending(): bool
    {
        return $this->recyclePending;
    }

    public function getRecycleReason(): string
    {
        return $this->recycleReason;
    }

    public function isDrainActive(): bool
    {
        if (!$this->recyclePending) {
            return false;
        }
        if ($this->drainSecond <= 0) {
            return false;
        }
        return microtime(true) < $this->drainDeadlineAt;
    }

    public function getDrainDeadlineAtFloat(): float
    {
        return $this->drainDeadlineAt;
    }

    private function installSignal(): void
    {
        $this->signalInstalled = true;

        if (PHP_SAPI !== 'cli') {
            return;
        }

        if (!function_exists('pcntl_signal')) {
            return;
        }

        $handler = function (int $signal): void {
            $this->markRecycle('signal');
        };

        // SIGTERM/SIGINT are common in containers for graceful stop.
        if (defined('SIGTERM')) {
            @pcntl_signal(SIGTERM, $handler);
        }
        if (defined('SIGINT')) {
            @pcntl_signal(SIGINT, $handler);
        }
        if (defined('SIGHUP')) {
            @pcntl_signal(SIGHUP, $handler);
        }
    }
}
