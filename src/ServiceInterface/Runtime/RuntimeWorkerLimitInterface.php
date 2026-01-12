<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerLimitInterface
{
    public function getMaxRequest(): int;

    public function getMaxUptimeSec(): int;

    public function getMaxMemoryMb(): int;

    public function getSoftMemoryMb(): int;
}
