<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Tool\Runtime\Fixture;

final class FixtureResetReport
{
    private int $count;
    private int $durationMs;

    /** @var array<int, array{name: string, durationMs: int, ok: bool, error: string}> */
    private array $item;

    /**
     * @param array<int, array{name: string, durationMs: int, ok: bool, error: string}> $item
     */
    public function __construct(int $count, int $durationMs, array $item)
    {
        $this->count = $count;
        $this->durationMs = $durationMs;
        $this->item = $item;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function getItem(): array
    {
        return $this->item;
    }
}
