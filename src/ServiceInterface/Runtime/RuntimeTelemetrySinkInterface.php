<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeTelemetrySinkInterface
{
    public function flush(RuntimeTelemetrySnapshot $snapshot): void;
}
