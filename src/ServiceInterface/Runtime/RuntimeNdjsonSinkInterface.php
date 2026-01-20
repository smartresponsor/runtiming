<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeNdjsonSinkInterface
{
    /** @param array<string,mixed> $event */
    public function emit(array $event): void;
}
