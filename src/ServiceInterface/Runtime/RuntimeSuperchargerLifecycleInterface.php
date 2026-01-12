<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeSuperchargerLifecycleInterface
{
    public function onRequestStart(): void;

    public function onResponse(int $statusCode, float $durationSec): object;

    public function onTerminate(int $statusCode, float $durationSec, object $decision): void;
}
