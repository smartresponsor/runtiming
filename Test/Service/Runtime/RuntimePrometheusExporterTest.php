<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Test\Service\Runtime;

use App\Service\Runtime\RuntimePrometheusExporter;
use App\ServiceInterface\Runtime\RuntimeTelemetrySnapshot;
use PHPUnit\Framework\TestCase;

final class RuntimePrometheusExporterTest extends TestCase
{
    public function testExportSanitizesLabelAndFormatsFloat(): void
    {
        $snap = new RuntimeTelemetrySnapshot('runtime');
        $snap->counter['runtime_supercharger_request_total{12="v",a-b="x"}'] = 7;
        $snap->gauge['runtime_supercharger_worker_uptime_second'] = 12.25;
        $snap->gauge['runtime_supercharger_memory_high_water_byte'] = INF;

        $exp = new RuntimePrometheusExporter();
        $out = $exp->export($snap, false);

        self::assertStringContainsString('# TYPE runtime_supercharger_request_total counter', $out);
        self::assertStringContainsString('runtime_supercharger_request_total{_12="v",a_b="x"} 7', $out);
        self::assertStringContainsString('runtime_supercharger_worker_uptime_second 12.25', $out);
        self::assertStringContainsString('runtime_supercharger_memory_high_water_byte 0', $out);
    }
}
