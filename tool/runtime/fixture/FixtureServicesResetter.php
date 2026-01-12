<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Tool\Runtime\Fixture;

final class FixtureServicesResetter
{
    private int $n = 0;

    public function reset(): void
    {
        $this->n++;
    }

    public function getN(): int
    {
        return $this->n;
    }
}
