<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeNdjsonSinkInterface;

final class RuntimeNullNdjsonSink implements RuntimeNdjsonSinkInterface
{
    /** @param array<string,mixed> $event */
    public function emit(array $event): void
    {
        // no-op
    }
}
