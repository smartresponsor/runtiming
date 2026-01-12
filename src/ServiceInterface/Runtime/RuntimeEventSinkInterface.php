<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeEventSinkInterface
{
    /** @param array<string, mixed> $payload */
    public function emit(string $type, array $payload): void;
}
