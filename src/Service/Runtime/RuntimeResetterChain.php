<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetterInterface;
use App\ServiceInterface\Runtime\RuntimeResetReport;

final class RuntimeResetterChain implements RuntimeResetterInterface
{
    /** @var array<int,RuntimeResetterInterface> */
    private array $resetter;

    private RuntimeResetReport $lastReport;

    /** @param array<int,RuntimeResetterInterface> $resetter */
    public function __construct(array $resetter)
    {
        $this->resetter = $resetter;
        $this->lastReport = new RuntimeResetReport();
    }

    public function reset(object $kernel): void
    {
        $this->lastReport = new RuntimeResetReport();

        foreach ($this->resetter as $r) {
            try {
                $r->reset($kernel);
                $this->lastReport->add(get_class($r), get_class($r), 'reset', true, '');
            } catch (\Throwable $e) {
                $this->lastReport->add(get_class($r), get_class($r), 'reset', false, $e->getMessage());
            }
        }
    }

    /** @return array<int,array<string,mixed>> */
    public function getLastReportAsArray(): array
    {
        return $this->lastReport->toArray();
    }
}
