<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeTelemetryInterface;
use App\ServiceInterface\Runtime\RuntimeTelemetrySnapshot;

final class RuntimeTelemetry implements RuntimeTelemetryInterface
{
    private string $namespace;

    /** @var callable():float */
    private $now;

    /** @var callable():int */
    private $memory;

    private float $workerStartAt = 0.0;
    private float $requestStartAt = 0.0;
    private string $requestEngine = 'unknown';

    /** @var array<string,int> */
    private array $counter = [];

    /** @var array<string,float> */
    private array $gauge = [];

    public function __construct(string $namespace = 'runtime', ?callable $now = null, ?callable $memory = null)
    {
        $this->namespace = $namespace;
        $this->now = $now ?? static fn (): float => microtime(true);
        $this->memory = $memory ?? static fn (): int => (int) memory_get_usage(true);

        $this->workerStartAt = ($this->now)();
        $this->gauge['runtime_supercharger_worker_start_time_second'] = $this->workerStartAt;
        $this->gauge['runtime_supercharger_memory_high_water_byte'] = (float) ($this->memory)();
    }

    public function beforeRequest(string $engine): void
    {
        $this->requestStartAt = ($this->now)();
        $this->requestEngine = $engine !== '' ? $engine : 'unknown';

        $this->updateUptime();
        $this->updateMemoryHighWater();
    }

    public function afterRequest(string $engine, int $status, bool $recycle, string $action, string $reason): void
    {
        $t = ($this->now)();
        $duration = $this->requestStartAt > 0.0 ? max(0.0, $t - $this->requestStartAt) : 0.0;

        $eng = $engine !== '' ? $engine : ($this->requestEngine !== '' ? $this->requestEngine : 'unknown');
        $statusLabel = (string) $status;

        $this->inc('runtime_supercharger_request_total', ['engine' => $eng, 'status' => $statusLabel], 1);
        $this->inc('runtime_supercharger_request_duration_count', ['engine' => $eng], 1);
        $this->add('runtime_supercharger_request_duration_sum', ['engine' => $eng], $duration);

        $maxKey = $this->metricKey('runtime_supercharger_request_duration_max', ['engine' => $eng]);
        $prevMax = $this->gauge[$maxKey] ?? 0.0;
        if ($duration > $prevMax) {
            $this->gauge[$maxKey] = $duration;
        }

        if ($recycle) {
            $act = $action !== '' ? $action : 'unknown';
            $rea = $reason !== '' ? $reason : 'unknown';
            $this->inc('runtime_supercharger_recycle_total', ['engine' => $eng, 'action' => $act, 'reason' => $rea], 1);
        }

        $this->updateUptime();
        $this->updateMemoryHighWater();
    }

    public function snapshot(): RuntimeTelemetrySnapshot
    {
        $snap = new RuntimeTelemetrySnapshot($this->namespace);

        $snap->counter = $this->counter;
        $snap->gauge = $this->gauge;
        $snap->meta = [
            'namespace' => [
                'value' => $this->namespace,
            ],
        ];

        return $snap;
    }

    public function reset(): void
    {
        $this->counter = [];
        $this->gauge = [];
        $this->workerStartAt = ($this->now)();
        $this->gauge['runtime_supercharger_worker_start_time_second'] = $this->workerStartAt;
        $this->gauge['runtime_supercharger_memory_high_water_byte'] = (float) ($this->memory)();
    }

    /** @param array<string,string> $label */
    private function inc(string $name, array $label, int $delta): void
    {
        $k = $this->metricKey($name, $label);
        $this->counter[$k] = ($this->counter[$k] ?? 0) + $delta;
    }

    /** @param array<string,string> $label */
    private function add(string $name, array $label, float $value): void
    {
        $k = $this->metricKey($name, $label);
        $this->gauge[$k] = ($this->gauge[$k] ?? 0.0) + $value;
    }

    private function updateUptime(): void
    {
        $now = ($this->now)();
        $this->gauge['runtime_supercharger_worker_uptime_second'] = max(0.0, $now - $this->workerStartAt);
    }

    private function updateMemoryHighWater(): void
    {
        $m = (float) ($this->memory)();
        $prev = $this->gauge['runtime_supercharger_memory_high_water_byte'] ?? 0.0;
        if ($m > $prev) {
            $this->gauge['runtime_supercharger_memory_high_water_byte'] = $m;
        }
    }

    /** @param array<string,string> $label */
    private function metricKey(string $name, array $label): string
    {
        if ($label === []) {
            return $name;
        }

        ksort($label);
        $buf = [];
        foreach ($label as $k => $v) {
            $buf[] = $k . '=' . $v;
        }

        return $name . '{' . implode(',', $buf) . '}';
    }
}
