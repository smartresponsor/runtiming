<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

final class FixtureKernelResetInner
{
    private int $count = 0;

    public function reset(): void
    {
        $this->count++;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
