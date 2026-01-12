<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

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
