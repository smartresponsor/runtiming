<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Tool\Runtime\Fixture;

use App\ServiceInterface\Runtime\RuntimeResetInterface;

final class FixtureCounterResetter implements RuntimeResetInterface
{
    private int $count = 0;

    public function getRuntimeResetName(): string
    {
        return 'fixture-counter';
    }

    public function reset(): void
    {
        $this->count++;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
