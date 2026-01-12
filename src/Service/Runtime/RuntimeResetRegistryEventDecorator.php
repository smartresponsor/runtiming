<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeNdjsonSinkInterface;
use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerEvent;
use Throwable;

final class RuntimeResetRegistryEventDecorator implements RuntimeResetRegistryInterface
{
    private RuntimeResetRegistryInterface $inner;
    private RuntimeNdjsonSinkInterface $sink;

    public function __construct(RuntimeResetRegistryInterface $inner, RuntimeNdjsonSinkInterface $sink)
    {
        $this->inner = $inner;
        $this->sink = $sink;
    }

    public function addResetter(object $resetter): void
    {
        $this->inner->addResetter($resetter);
    }

    public function resetAll(): object
    {
        $report = $this->inner->resetAll();

        try {
            $payload = $this->exportReport($report);
            $event = (new RuntimeSuperchargerEvent('runtime.reset', ['resetReport' => $payload]))->toArray();
            $this->sink->emit($event);
        } catch (Throwable $e) {
            // ignore
        }

        return $report;
    }

    /** @return array<string,mixed> */
    private function exportReport(object $report): array
    {
        if (method_exists($report, 'toArray')) {
            $arr = $report->toArray();
            if (is_array($arr)) {
                return $arr;
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
