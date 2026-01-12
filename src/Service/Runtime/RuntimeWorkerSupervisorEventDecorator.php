<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeNdjsonSinkInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerEvent;
use App\ServiceInterface\Runtime\RuntimeWorkerSupervisorInterface;
use Throwable;

final class RuntimeWorkerSupervisorEventDecorator implements RuntimeWorkerSupervisorInterface
{
    private RuntimeWorkerSupervisorInterface $inner;
    private RuntimeNdjsonSinkInterface $sink;

    public function __construct(RuntimeWorkerSupervisorInterface $inner, RuntimeNdjsonSinkInterface $sink)
    {
        $this->inner = $inner;
        $this->sink = $sink;
    }

    public function afterRequest(int $statusCode): object
    {
        $decision = $this->inner->afterRequest($statusCode);

        try {
            $payload = $this->exportDecision($decision);
            $event = (new RuntimeSuperchargerEvent('runtime.workerDecision', ['decision' => $payload]))->toArray();
            $this->sink->emit($event);
        } catch (Throwable $e) {
            // ignore
        }

        return $decision;
    }

    public function reset(): void
    {
        $this->inner->reset();
    }

    /** @return array<string,mixed> */
    private function exportDecision(object $decision): array
    {
        if (method_exists($decision, 'toArray')) {
            $arr = $decision->toArray();
            if (is_array($arr)) {
                return $arr;
            }
        }

        if (method_exists($decision, 'getReason') && method_exists($decision, 'getShouldRecycle')) {
            return [
                'reason' => (string) $decision->getReason(),
                'shouldRecycle' => (bool) $decision->getShouldRecycle(),
            ];
        }

        return ['class' => get_class($decision)];
    }
}
