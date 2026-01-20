<?php
declare(strict_types=1);



namespace App\Tool\Runtime\Fixture;

use App\ServiceInterface\Runtime\RuntimeEventSinkInterface;

final class FixtureMemorySink implements RuntimeEventSinkInterface
{
    /** @var array<int, array{type: string, payload: array<string, mixed>}> */
    private array $event = [];

    public function emit(string $type, array $payload): void
    {
        $this->event[] = [
            'type' => $type,
            'payload' => $payload,
        ];
    }

    /** @return array<int, array{type: string, payload: array<string, mixed>}> */
    public function listEvent(): array
    {
        return $this->event;
    }
}
