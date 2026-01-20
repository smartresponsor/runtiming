<?php
declare(strict_types=1);



namespace App\Tool\Runtime;

use App\ServiceInterface\Runtime\RuntimeEventSinkInterface;
use App\Tool\Runtime\Fixture\FixtureWorkerSupervisorInterface;
use App\Tool\Runtime\Fixture\FixtureWorkerDecision;

final class ToolWorkerSupervisorEventDecorator implements FixtureWorkerSupervisorInterface
{
    private FixtureWorkerSupervisorInterface $inner;
    private RuntimeEventSinkInterface $sink;

    public function __construct(FixtureWorkerSupervisorInterface $inner, RuntimeEventSinkInterface $sink)
    {
        $this->inner = $inner;
        $this->sink = $sink;
    }

    public function afterRequest(int $statusCode): FixtureWorkerDecision
    {
        $decision = $this->inner->afterRequest($statusCode);
        $this->sink->emit('runtime-worker-decision', $decision->toArray());
        return $decision;
    }
}
