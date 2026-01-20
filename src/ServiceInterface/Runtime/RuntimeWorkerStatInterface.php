<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerStatInterface
{
    public function getUptimeSec(): int;

    public function getRssMemoryMb(): int;

    public function incRequest(): int;

    public function reset(): void;
}
