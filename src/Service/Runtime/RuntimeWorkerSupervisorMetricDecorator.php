<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerMetricEmitterInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerSupervisorInterface;

final class RuntimeWorkerSupervisorMetricDecorator implements RuntimeWorkerSupervisorInterface
{
    private RuntimeWorkerSupervisorInterface $inner;
    private RuntimeSuperchargerMetricEmitterInterface $emitter;

    public function __construct(RuntimeWorkerSupervisorInterface $inner, RuntimeSuperchargerMetricEmitterInterface $emitter)
    {
        $this->inner = $inner;
        $this->emitter = $emitter;
    }

    public function afterRequest(int $statusCode): object
    {
        $decision = $this->inner->afterRequest($statusCode);
        if (is_object($decision)) {
            $this->emitter->onWorkerDecision($decision);
        }
        return $decision;
    }

    public function reset(): void
    {
        $this->inner->reset();
    }
}
