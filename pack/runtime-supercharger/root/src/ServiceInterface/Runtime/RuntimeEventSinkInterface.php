<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeEventSinkInterface
{
    /** @param array<string, mixed> $payload */
    public function emit(string $type, array $payload): void;
}
