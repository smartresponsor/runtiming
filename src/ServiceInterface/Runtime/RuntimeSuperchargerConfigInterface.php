<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeSuperchargerConfigInterface
{
    public function getBeforeEnable(): bool;

    public function getAfterEnable(): bool;

    public function getGcEnable(): bool;

    public function getMaxRequest(): int;

    public function getMaxUptimeSec(): int;

    public function getSoftMemoryMb(): int;

    public function getMaxMemoryMb(): int;

    public function getFeedPath(): string;

    public function getFeedMaxBytes(): int;

    public function getFeedMaxKeep(): int;

    /** @return array<string,mixed> */
    public function toArray(): array;
}
