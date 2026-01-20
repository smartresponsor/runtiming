<?php
declare(strict_types=1);



namespace App\Tool\Runtime\Fixture;

use App\ServiceInterface\Runtime\RuntimeResetInterface;
use RuntimeException;

final class FixtureErrorResetter implements RuntimeResetInterface
{
    private bool $boom;

    public function __construct(bool $boom = true)
    {
        $this->boom = $boom;
    }

    public function getRuntimeResetName(): string
    {
        return 'fixture-error';
    }

    public function reset(): void
    {
        if ($this->boom) {
            throw new RuntimeException('fixture reset boom');
        }
    }
}
