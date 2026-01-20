<?php
declare(strict_types=1);



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
