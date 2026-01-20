<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RunnerResponse;
use App\ServiceInterface\Runtime\RuntimeLifecycleDecision;

final class RuntimeLifecycleHeaderInjector
{
    public function apply(RunnerResponse $response, RuntimeLifecycleDecision $decision): RunnerResponse
    {
        if (!$decision->recycle) {
            return $response;
        }

        $res = $response
            ->withHeader('X-Runtime-Supercharger-Recycle', '1')
            ->withHeader('X-Runtime-Supercharger-Action', $decision->action)
            ->withHeader('X-Runtime-Supercharger-Reason', $decision->reason);

        // Keep meta off headers by default; engines can log meta from decision object.
        return $res;
    }
}
