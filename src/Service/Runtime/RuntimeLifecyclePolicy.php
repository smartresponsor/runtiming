<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeLifecycleConfig;
use App\ServiceInterface\Runtime\RuntimeLifecycleDecision;
use App\ServiceInterface\Runtime\RuntimeLifecyclePolicyInterface;
use App\ServiceInterface\Runtime\RuntimeLifecycleState;

final class RuntimeLifecyclePolicy implements RuntimeLifecyclePolicyInterface
{
    private RuntimeLifecycleConfig $config;
    private RuntimeLifecycleState $state;

    /** @var callable():float */
    private $now;

    /** @var callable():int */
    private $memory;

    /** @var callable(int,int):int */
    private $randInt;

    private float $requestStartAt = 0.0;
    private bool $booted = false;

    /**
     * @param callable():float $now
     * @param callable():int $memory
     * @param callable(int,int):int $randInt
     */
    public function __construct(
        RuntimeLifecycleConfig $config,
        ?callable $now = null,
        ?callable $memory = null,
        ?callable $randInt = null
    ) {
        $this->config = $config;
        $this->state = new RuntimeLifecycleState();

        $this->now = $now ?? static fn (): float => microtime(true);
        $this->memory = $memory ?? static fn (): int => (int) memory_get_usage(true);
        $this->randInt = $randInt ?? static fn (int $min, int $max): int => random_int($min, $max);
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $t = ($this->now)();
        $m = ($this->memory)();

        $this->state->startAt = $t;
        $this->state->lastAt = $t;
        $this->state->baselineMemoryByte = $m;
        $this->state->memoryHighWaterByte = $m;
        $this->state->lastMemoryByte = $m;

        $this->state->effectiveMaxRequest = $this->jitterInt($this->config->maxRequest, $this->config->jitterPercent);
        $this->state->effectiveMaxUptimeSec = $this->jitterFloat($this->config->maxUptimeSec, $this->config->jitterPercent);
        $this->state->effectiveMaxIdleSec = $this->jitterFloat($this->config->maxIdleSec, $this->config->jitterPercent);

        $this->booted = true;
    }

    public function beforeRequest(): void
    {
        $this->boot();

        $t = ($this->now)();
        $this->requestStartAt = $t;

        $idle = max(0.0, $t - $this->state->lastAt);
        $this->state->lastIdleSec = $idle;
    }

    public function afterRequest(): RuntimeLifecycleDecision
    {
        $this->boot();

        $t = ($this->now)();
        $m = ($this->memory)();

        $duration = $this->requestStartAt > 0.0 ? max(0.0, $t - $this->requestStartAt) : 0.0;

        $this->state->requestCount++;
        $this->state->lastAt = $t;
        $this->state->lastDurationSec = $duration;
        $this->state->lastMemoryByte = $m;
        if ($m > $this->state->memoryHighWaterByte) {
            $this->state->memoryHighWaterByte = $m;
        }

        return $this->decide($t, $m, $duration);
    }

    public function getState(): RuntimeLifecycleState
    {
        return $this->state;
    }

    private function decide(float $now, int $memoryByte, float $durationSec): RuntimeLifecycleDecision
    {
        $uptime = max(0.0, $now - $this->state->startAt);
        $idle = $this->state->lastIdleSec;

        $meta = [
            'requestCount' => (string) $this->state->requestCount,
            'uptimeSec' => (string) $uptime,
            'idleSec' => (string) $idle,
            'durationSec' => (string) $durationSec,
            'memoryByte' => (string) $memoryByte,
            'baselineMemoryByte' => (string) $this->state->baselineMemoryByte,
            'memoryHighWaterByte' => (string) $this->state->memoryHighWaterByte,
        ];

        if ($this->config->emergencyMemoryByte > 0 && $memoryByte >= $this->config->emergencyMemoryByte) {
            return new RuntimeLifecycleDecision(true, 'hardExit', 'emergencyMemory', $meta);
        }

        if ($this->config->maxMemoryByte > 0 && $memoryByte >= $this->config->maxMemoryByte) {
            return new RuntimeLifecycleDecision(true, 'gracefulExit', 'maxMemory', $meta);
        }

        if ($this->state->effectiveMaxRequest > 0 && $this->state->requestCount >= $this->state->effectiveMaxRequest) {
            return new RuntimeLifecycleDecision(true, 'gracefulExit', 'maxRequest', $meta);
        }

        if ($this->state->effectiveMaxUptimeSec > 0.0 && $uptime >= $this->state->effectiveMaxUptimeSec) {
            return new RuntimeLifecycleDecision(true, 'gracefulExit', 'maxUptime', $meta);
        }

        if ($this->state->effectiveMaxIdleSec > 0.0 && $idle >= $this->state->effectiveMaxIdleSec) {
            return new RuntimeLifecycleDecision(true, 'gracefulExit', 'maxIdle', $meta);
        }

        if ($this->config->maxRequestDurationSec > 0.0 && $durationSec >= $this->config->maxRequestDurationSec) {
            return new RuntimeLifecycleDecision(true, 'gracefulExit', 'maxRequestDuration', $meta);
        }

        if ($this->config->maxMemoryGrowthByte > 0) {
            $growth = max(0, $this->state->memoryHighWaterByte - $this->state->baselineMemoryByte);
            if ($growth >= $this->config->maxMemoryGrowthByte) {
                $meta['memoryGrowthByte'] = (string) $growth;
                return new RuntimeLifecycleDecision(true, 'gracefulExit', 'memoryGrowth', $meta);
            }
        }

        return RuntimeLifecycleDecision::none();
    }

    private function jitterInt(int $value, float $jitterPercent): int
    {
        if ($value <= 0) {
            return 0;
        }
        if ($jitterPercent <= 0.0) {
            return $value;
        }

        $delta = (int) max(1, floor($value * $jitterPercent));
        $min = max(1, $value - $delta);
        $max = $value + $delta;

        return ($this->randInt)($min, $max);
    }

    private function jitterFloat(float $value, float $jitterPercent): float
    {
        if ($value <= 0.0) {
            return 0.0;
        }
        if ($jitterPercent <= 0.0) {
            return $value;
        }

        $delta = $value * $jitterPercent;
        $min = max(0.0, $value - $delta);
        $max = $value + $delta;

        // Convert to integer range to keep deterministic bounds under random_int.
        $scale = 1000;
        $imin = (int) floor($min * $scale);
        $imax = (int) ceil($max * $scale);
        $r = ($this->randInt)($imin, max($imin, $imax));

        return $r / $scale;
    }
}
