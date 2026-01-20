<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeEventSinkInterface
{
    /** @param array<string, mixed> $payload */
    public function emit(string $type, array $payload): void;
}
