<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerStateInterface
{
    public function onRequestStart(): void;

    public function getStartAtFloat(): float;

    public function getRequestCount(): int;

    public function getUptimeSecond(): int;

    public function getMemoryUsageMb(): int;

    public function getMemoryPeakMb(): int;

    public function markRecycle(string $reason): void;

    public function isRecyclePending(): bool;

    public function getRecycleReason(): string;

    public function isDrainActive(): bool;

    public function getDrainDeadlineAtFloat(): float;
}
