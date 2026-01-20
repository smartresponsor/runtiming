<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Http\Runtime;

use App\Service\Runtime\RuntimeStatusProvider;
use App\Service\Runtime\RuntimeTelemetryDirInspector;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\HttpInterface\Runtime\RuntimeStatusControllerInterface;

final class RuntimeStatusController implements RuntimeStatusControllerInterface
{
    private RuntimeStatusProvider $provider;
    private RuntimeTelemetryDirInspector $inspector;

    public function __construct(RuntimeStatusProvider $provider, RuntimeTelemetryDirInspector $inspector)
    {
        $this->provider = $provider;
        $this->inspector = $inspector;
    }

    public function worker(): JsonResponse
    {
        $s = $this->provider->snapshot();

        return new JsonResponse([
            'engine' => $s->engine,
            'workerId' => $s->workerId,
            'php' => $s->php,
            'workerStartTime' => $s->workerStartTime,
            'workerUptime' => $s->workerUptime,
            'memoryUsageByte' => $s->memoryUsageByte,
            'memoryUsageRealByte' => $s->memoryUsageRealByte,
            'memoryPeakByte' => $s->memoryPeakByte,
            'memoryPeakRealByte' => $s->memoryPeakRealByte,
            'extra' => $s->extra,
        ], 200, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function host(): JsonResponse
    {
        $i = $this->inspector->inspect();

        return new JsonResponse([
            'telemetry' => $i,
        ], 200, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
