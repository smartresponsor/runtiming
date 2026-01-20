<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

final class RuntimeResetReport
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

    /** @return array<int, array{name: string, durationMs: int, ok: bool, error: string}> */
    public function getItem(): array
    {
        return $this->item;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'count' => $this->count,
            'durationMs' => $this->durationMs,
            'item' => $this->item,
        ];
    }
}
