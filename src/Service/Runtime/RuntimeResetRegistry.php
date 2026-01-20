<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeResetReport;
use App\ServiceInterface\Runtime\RuntimeResetterInterface;

final class RuntimeResetRegistry implements RuntimeResetRegistryInterface
{
    /**
     * @var iterable<RuntimeResetterInterface>
     */
    private iterable $resetter;

    /**
     * @param iterable<RuntimeResetterInterface> $resetter
     */
    public function __construct(iterable $resetter)
    {
        $this->resetter = $resetter;
    }

    public function resetAll(): RuntimeResetReport
    {
        $report = new RuntimeResetReport();
        $start = microtime(true);

        foreach ($this->resetter as $r) {
            try {
                $r->reset($report);
                $report->resetCount++;
            } catch (\Throwable $e) {
                $report->addError(get_class($r) . ': ' . $e->getMessage());
            }
        }

        $report->durationMs = (int) floor((microtime(true) - $start) * 1000);

        return $report;
    }
}
