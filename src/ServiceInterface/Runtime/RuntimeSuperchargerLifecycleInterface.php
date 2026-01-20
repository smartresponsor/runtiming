<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeSuperchargerLifecycleInterface
{
    public function onRequestStart(): void;

    public function onResponse(int $statusCode, float $durationSec): object;

    public function onTerminate(int $statusCode, float $durationSec, object $decision): void;
}
