<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Tool\Runtime\Fixture;

final class FixtureResetRegistry implements FixtureResetRegistryInterface
{
    private int $n = 0;

    public function resetAll(): FixtureResetReport
    {
        $this->n++;
        $item = [[
            'name' => 'fixture',
            'durationMs' => 1,
            'ok' => true,
            'error' => '',
        ]];

        return new FixtureResetReport(1, 1, $item);
    }

    public function getN(): int
    {
        return $this->n;
    }
}
