<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

final class RuntimeSymfonyKernelTerminateListener
{
    private RuntimeSymfonyResetOrchestrator $orchestrator;

    public function __construct(RuntimeSymfonyResetOrchestrator $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    public function onKernelTerminate(object $event): void
    {
        if (!$this->isMainRequest($event)) {
            return;
        }

        $this->orchestrator->afterResponse();
    }

    private function isMainRequest(object $event): bool
    {
        // Symfony 5.3+ uses isMainRequest(); older versions had isMasterRequest().
        if (method_exists($event, 'isMainRequest')) {
            return (bool) $event->isMainRequest();
        }

        if (method_exists($event, 'isMasterRequest')) {
            return (bool) $event->isMasterRequest();
        }

        // Best effort fallback: treat as main request.
        return true;
    }
}
