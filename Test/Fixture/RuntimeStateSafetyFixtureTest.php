<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Test\Fixture;

use App\ServiceInterface\Runtime\RuntimeResetReport;
use App\ServiceInterface\Runtime\RuntimeResetterInterface;
use PHPUnit\Framework\TestCase;

final class RuntimeStateSafetyFixtureTest extends TestCase
{
    public const SEED = [
        ['requestId' => 'req-1', 'value' => 10],
        ['requestId' => 'req-2', 'value' => 20],
    ];

    public static function probe(): RuntimeStateProbe
    {
        return new RuntimeStateProbe();
    }

    public static function resetter(RuntimeStateProbe $probe): RuntimeResetterInterface
    {
        return new RuntimeStateResetter($probe);
    }
}

final class RuntimeStateProbe
{
    private ?string $requestId = null;
    private ?int $value = null;

    public function store(string $requestId, int $value): void
    {
        $this->requestId = $requestId;
        $this->value = $value;
    }

    public function state(): array
    {
        return [
            'requestId' => $this->requestId,
            'value' => $this->value,
        ];
    }

    public function resetState(): void
    {
        $this->requestId = null;
        $this->value = null;
    }
}

final class RuntimeStateResetter implements RuntimeResetterInterface
{
    private RuntimeStateProbe $probe;

    public function __construct(RuntimeStateProbe $probe)
    {
        $this->probe = $probe;
    }

    public function reset(RuntimeResetReport $report): void
    {
        $this->probe->resetState();
    }
}
