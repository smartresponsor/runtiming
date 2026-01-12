<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeMetricRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerMetricEmitterInterface;

final class RuntimeSuperchargerMetricEmitter implements RuntimeSuperchargerMetricEmitterInterface
{
    private RuntimeMetricRegistryInterface $registry;

    public function __construct(RuntimeMetricRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function onReset(object $report, float $durationSec, bool $ok): void
    {
        $this->registry->incCounter('runtime_supercharger_reset_total', ['result' => $ok ? 'ok' : 'fail'], 1);
        $this->registry->observeSummary('runtime_supercharger_reset_duration_seconds', max(0.0, $durationSec), []);

        $count = $this->tryCount($report);
        if ($count !== null) {
            $this->registry->setGauge('runtime_supercharger_reset_count', (float) $count, []);
        }
    }

    public function onWorkerDecision(object $decision): void
    {
        $reason = $this->tryReason($decision) ?? 'unknown';
        $recycle = $this->tryRecycle($decision) ? '1' : '0';

        $this->registry->incCounter('runtime_supercharger_worker_decision_total', [
            'reason' => $reason,
            'recycle' => $recycle,
        ], 1);
    }

    public function onSnapshot(int $rssMemoryMb, int $uptimeSec, int $requestCount): void
    {
        $this->registry->setGauge('runtime_supercharger_rss_memory_mb', (float) max(0, $rssMemoryMb), []);
        $this->registry->setGauge('runtime_supercharger_uptime_sec', (float) max(0, $uptimeSec), []);
        $this->registry->setGauge('runtime_supercharger_request_count', (float) max(0, $requestCount), []);
    }

    private function tryCount(object $report): ?int
    {
        if (method_exists($report, 'getCount')) {
            $n = $report->getCount();
            return is_int($n) ? $n : null;
        }
        if (method_exists($report, 'toArray')) {
            $a = $report->toArray();
            if (is_array($a) && isset($a['count']) && is_int($a['count'])) {
                return $a['count'];
            }
        }
        return null;
    }

    private function tryReason(object $decision): ?string
    {
        if (method_exists($decision, 'getReason')) {
            $r = $decision->getReason();
            return is_string($r) ? $r : (string) $r;
        }
        if (method_exists($decision, 'toArray')) {
            $a = $decision->toArray();
            if (is_array($a) && isset($a['reason']) && is_string($a['reason'])) {
                return $a['reason'];
            }
        }
        return null;
    }

    private function tryRecycle(object $decision): bool
    {
        if (method_exists($decision, 'getShouldRecycle')) {
            return (bool) $decision->getShouldRecycle();
        }
        if (method_exists($decision, 'toArray')) {
            $a = $decision->toArray();
            if (is_array($a) && isset($a['shouldRecycle'])) {
                return (bool) $a['shouldRecycle'];
            }
        }
        return false;
    }
}
