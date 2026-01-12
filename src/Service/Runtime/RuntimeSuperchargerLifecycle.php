<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeMetricExporterInterface;
use App\ServiceInterface\Runtime\RuntimeMetricRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeNdjsonSinkInterface;
use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerLifecycleInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerMetricEmitterInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerStateInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerSupervisorInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerEvent;
use Throwable;

final class RuntimeSuperchargerLifecycle implements RuntimeSuperchargerLifecycleInterface
{
    private RuntimeSuperchargerConfigInterface $config;
    private RuntimeWorkerStateInterface $state;
    private RuntimeResetRegistryInterface $resetRegistry;
    private RuntimeWorkerSupervisorInterface $workerSupervisor;
    private RuntimeNdjsonSinkInterface $sink;
    private RuntimeSuperchargerMetricEmitterInterface $metricEmitter;
    private RuntimeMetricExporterInterface $metricExporter;
    private ?RuntimeMetricRegistryInterface $metricRegistry;
    private RuntimeDecisionInspector $inspector;

    public function __construct(
        RuntimeSuperchargerConfigInterface $config,
        RuntimeWorkerStateInterface $state,
        RuntimeResetRegistryInterface $resetRegistry,
        RuntimeWorkerSupervisorInterface $workerSupervisor,
        RuntimeNdjsonSinkInterface $sink,
        RuntimeSuperchargerMetricEmitterInterface $metricEmitter,
        RuntimeMetricExporterInterface $metricExporter,
        ?RuntimeMetricRegistryInterface $metricRegistry = null,
        ?RuntimeDecisionInspector $inspector = null
    ) {
        $this->config = $config;
        $this->state = $state;
        $this->resetRegistry = $resetRegistry;
        $this->workerSupervisor = $workerSupervisor;
        $this->sink = $sink;
        $this->metricEmitter = $metricEmitter;
        $this->metricExporter = $metricExporter;
        $this->metricRegistry = $metricRegistry;
        $this->inspector = $inspector ?? new RuntimeDecisionInspector();
    }

    public function onRequestStart(): void
    {
        $this->state->onRequestStart();

        $this->bestEffort(function (): void {
            $this->metricEmitter->onSnapshot(
                $this->memoryMb(),
                $this->state->getUptimeSec(),
                $this->state->getRequestCount()
            );
        });
    }

    public function onResponse(int $statusCode, float $durationSec): object
    {
        $decision = $this->workerSupervisor->afterRequest($statusCode);

        $this->bestEffort(function () use ($decision): void {
            $payload = $this->exportDecision($decision);

            $event = (new RuntimeSuperchargerEvent('runtime.workerDecision', ['decision' => $payload]))->toArray();
            $this->sink->emit($event);
        });

        $this->bestEffort(function () use ($decision): void {
            $this->metricEmitter->onWorkerDecision($decision);
        });

        $this->bestEffort(function (): void {
            $this->metricEmitter->onSnapshot(
                $this->memoryMb(),
                $this->state->getUptimeSec(),
                $this->state->getRequestCount()
            );
        });

        $this->bestEffort(function (): void {
            if ($this->metricRegistry !== null) {
                $this->metricExporter->export($this->metricRegistry);
            }
        });

        return $decision;
    }

    public function onTerminate(int $statusCode, float $durationSec, object $decision): void
    {
        if (!$this->config->getAfterEnable()) {
            return;
        }

        $ok = true;
        $t0 = microtime(true);

        try {
            $report = $this->resetRegistry->resetAll();
        } catch (Throwable $e) {
            $ok = false;
            $report = null;
        }

        $resetDuration = microtime(true) - $t0;

        if ($this->config->getGcEnable()) {
            $this->bestEffort(static function (): void {
                gc_collect_cycles();
            });
        }

        if (is_object($report)) {
            $this->bestEffort(function () use ($report, $resetDuration, $ok): void {
                $this->metricEmitter->onReset($report, (float) $resetDuration, $ok);
            });

            $this->bestEffort(function () use ($report, $resetDuration, $ok): void {
                $payload = $this->exportReport($report);
                $event = (new RuntimeSuperchargerEvent('runtime.reset', [
                    'resetReport' => $payload,
                    'durationSec' => $resetDuration,
                    'ok' => $ok,
                ]))->toArray();

                $this->sink->emit($event);
            });
        }

        $this->bestEffort(function (): void {
            $this->metricEmitter->onSnapshot(
                $this->memoryMb(),
                $this->state->getUptimeSec(),
                $this->state->getRequestCount()
            );
        });

        $this->bestEffort(function (): void {
            if ($this->metricRegistry !== null) {
                $this->metricExporter->export($this->metricRegistry);
            }
        });
    }

    public function shouldRecycle(object $decision): bool
    {
        return $this->inspector->shouldRecycle($decision);
    }

    public function recycleReason(object $decision): string
    {
        return $this->inspector->reason($decision);
    }

    private function memoryMb(): int
    {
        $b = memory_get_usage(true);
        if (!is_int($b) || $b < 0) {
            return 0;
        }
        return (int) floor($b / (1024 * 1024));
    }

    private function bestEffort(callable $fn): void
    {
        try {
            $fn();
        } catch (Throwable $e) {
            // best-effort by design
        }
    }

    /** @return array<string,mixed> */
    private function exportDecision(object $decision): array
    {
        if (method_exists($decision, 'toArray')) {
            $a = $decision->toArray();
            if (is_array($a)) {
                return $a;
            }
        }

        return [
            'class' => get_class($decision),
            'shouldRecycle' => $this->inspector->shouldRecycle($decision),
            'reason' => $this->inspector->reason($decision),
        ];
    }

    /** @return array<string,mixed> */
    private function exportReport(object $report): array
    {
        if (method_exists($report, 'toArray')) {
            $a = $report->toArray();
            if (is_array($a)) {
                return $a;
            }
        }

        if (method_exists($report, 'getCount')) {
            $n = $report->getCount();
            if (is_int($n)) {
                return ['count' => $n];
            }
        }

        return ['class' => get_class($report)];
    }
}
