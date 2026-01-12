<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Test\Service\Runtime;

use App\Infra\Runtime\RuntimeResetterRegistry;
use App\Infra\Runtime\RuntimeWarmerRegistry;
use App\InfraInterface\Runtime\RuntimeWarmerInterface;
use App\Service\Runtime\RuntimeSuperchargerService;
use App\Service\Runtime\RuntimeWarmupService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Contracts\Service\ResetInterface;

final class RuntimeDemoScenarioTest extends TestCase
{
    public function testDeterministicDemoScenario(): void
    {
        $events = [];

        $warmerA = new class($events) implements RuntimeWarmerInterface {
            /** @var array<int, string> */
            private array $events;

            /** @param array<int, string> $events */
            public function __construct(array &$events)
            {
                $this->events = &$events;
            }

            public function warm(): void
            {
                $this->events[] = 'warm:a';
            }
        };

        $warmerB = new class($events) implements RuntimeWarmerInterface {
            /** @var array<int, string> */
            private array $events;

            /** @param array<int, string> $events */
            public function __construct(array &$events)
            {
                $this->events = &$events;
            }

            public function warm(): void
            {
                $this->events[] = 'warm:b';
            }
        };

        $resetterA = new class($events) implements ResetInterface {
            /** @var array<int, string> */
            private array $events;

            /** @param array<int, string> $events */
            public function __construct(array &$events)
            {
                $this->events = &$events;
            }

            public function reset(): void
            {
                $this->events[] = 'reset:a';
            }
        };

        $resetterB = new class($events) implements ResetInterface {
            /** @var array<int, string> */
            private array $events;

            /** @param array<int, string> $events */
            public function __construct(array &$events)
            {
                $this->events = &$events;
            }

            public function reset(): void
            {
                $this->events[] = 'reset:b';
            }
        };

        $warmupService = new RuntimeWarmupService(
            new RuntimeWarmerRegistry([$warmerA, $warmerB]),
            new NullLogger()
        );

        $superchargerService = new RuntimeSuperchargerService(
            new RuntimeResetterRegistry([$resetterA, $resetterB]),
            new NullLogger()
        );

        $warmupService->warmupOnBoot();
        $warmupService->warmupOnBoot();

        $superchargerService->resetAfterRequest();
        $superchargerService->resetAfterRequest();

        self::assertSame([
            'warm:a',
            'warm:b',
            'reset:a',
            'reset:b',
            'reset:a',
            'reset:b',
        ], $events);
    }
}
