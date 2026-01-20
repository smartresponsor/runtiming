<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeTelemetryInterface
{
    public function beforeRequest(string $engine): void;

    public function afterRequest(string $engine, int $status, bool $recycle, string $action, string $reason): void;

    public function snapshot(): RuntimeTelemetrySnapshot;

    public function reset(): void;
}
