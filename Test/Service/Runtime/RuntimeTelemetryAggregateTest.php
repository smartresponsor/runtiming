<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Test\Service\Runtime;

use App\Service\Runtime\RuntimeTelemetryAggregate;
use App\Service\Runtime\RuntimeTelemetryJsonCodec;
use PHPUnit\Framework\TestCase;

final class RuntimeTelemetryAggregateTest extends TestCase
{
    public function testAggregateMergesCounterAndGaugeDeterministically(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rt-telemetry-' . bin2hex(random_bytes(6));
        mkdir($dir, 0777, true);

        $one = [
            'namespace' => 'app',
            'counter' => [
                'runtime_supercharger_request_total{engine="rr"}' => 1,
            ],
            'gauge' => [
                'runtime_supercharger_request_duration_sum' => 2.5,
                'runtime_supercharger_request_duration_max' => 1.0,
                'runtime_supercharger_worker_start_time_second' => 1000,
                'runtime_supercharger_worker_uptime_second{pid="1"}' => 10,
            ],
        ];

        $two = [
            'namespace' => '',
            'counter' => [
                'runtime_supercharger_request_total{engine="rr"}' => 2,
            ],
            'gauge' => [
                'runtime_supercharger_request_duration_sum' => 3.5,
                'runtime_supercharger_request_duration_max' => 1.2,
                'runtime_supercharger_worker_start_time_second' => 900,
                'runtime_supercharger_worker_uptime_second{pid="1"}' => 12,
            ],
        ];

        file_put_contents($dir . DIRECTORY_SEPARATOR . 'a.json', json_encode($one, JSON_THROW_ON_ERROR));
        file_put_contents($dir . DIRECTORY_SEPARATOR . 'b.json', json_encode($two, JSON_THROW_ON_ERROR));

        $agg = new RuntimeTelemetryAggregate($dir, new RuntimeTelemetryJsonCodec());
        $snap = $agg->aggregate();

        self::assertSame('app', $snap->namespace);
        self::assertSame(3, $snap->counter['runtime_supercharger_request_total{engine="rr"}'] ?? null);

        self::assertSame(6.0, $snap->gauge['runtime_supercharger_request_duration_sum'] ?? null);
        self::assertSame(1.2, $snap->gauge['runtime_supercharger_request_duration_max'] ?? null);

        self::assertSame(900.0, $snap->gauge['runtime_supercharger_worker_start_time_second'] ?? null);
        self::assertSame(12.0, $snap->gauge['runtime_supercharger_worker_uptime_second{pid="1"}'] ?? null);

        // cleanup
        @unlink($dir . DIRECTORY_SEPARATOR . 'a.json');
        @unlink($dir . DIRECTORY_SEPARATOR . 'b.json');
        @rmdir($dir);
    }
}
