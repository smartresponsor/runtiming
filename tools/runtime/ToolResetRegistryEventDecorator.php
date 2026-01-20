<?php
declare(strict_types=1);



namespace App\Tool\Runtime;

use App\ServiceInterface\Runtime\RuntimeEventSinkInterface;
use App\Tool\Runtime\Fixture\FixtureResetRegistryInterface;
use App\Tool\Runtime\Fixture\FixtureResetReport;

final class ToolResetRegistryEventDecorator implements FixtureResetRegistryInterface
{
    private FixtureResetRegistryInterface $inner;
    private RuntimeEventSinkInterface $sink;

    public function __construct(FixtureResetRegistryInterface $inner, RuntimeEventSinkInterface $sink)
    {
        $this->inner = $inner;
        $this->sink = $sink;
    }

    public function resetAll(): FixtureResetReport
    {
        $report = $this->inner->resetAll();

        $this->sink->emit('runtime-reset-report', [
            'count' => $report->getCount(),
            'durationMs' => $report->getDurationMs(),
            'item' => $report->getItem(),
        ]);

        return $report;
    }
}
