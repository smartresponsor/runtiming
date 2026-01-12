<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerStatInterface
{
    public function getUptimeSec(): int;

    public function getRssMemoryMb(): int;

    public function incRequest(): int;

    public function reset(): void;
}
