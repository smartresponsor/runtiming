<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Http\Runtime;

use App\Service\Runtime\RuntimePrometheusExporter;
use App\Service\Runtime\RuntimeTelemetryAggregate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\HttpInterface\Runtime\RuntimeMetricsControllerInterface;

final class RuntimeMetricsController implements RuntimeMetricsControllerInterface
{
    private RuntimeTelemetryAggregate $aggregate;
    private RuntimePrometheusExporter $exporter;

    public function __construct(RuntimeTelemetryAggregate $aggregate, RuntimePrometheusExporter $exporter)
    {
        $this->aggregate = $aggregate;
        $this->exporter = $exporter;
    }

    public function metrics(): Response
    {
        $snap = $this->aggregate->aggregate();
        $text = $this->exporter->export($snap, true);

        return new Response($text, 200, [
            'Content-Type' => 'text/plain; version=0.0.4',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function aggregate(): JsonResponse
    {
        $snap = $this->aggregate->aggregate();

        return new JsonResponse([
            'namespace' => $snap->namespace,
            'counter' => $snap->counter,
            'gauge' => $snap->gauge,
            'meta' => $snap->meta,
        ], 200, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
