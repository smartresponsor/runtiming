<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Test\Service\Runtime;

use App\Service\Runtime\RuntimeResetRegistry;
use App\Service\Runtime\RuntimeSuperchargerService;
use App\ServiceInterface\Runtime\RuntimeResetReport;
use App\ServiceInterface\Runtime\RuntimeResetterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class RuntimeSuperchargerServiceTest extends TestCase
{
    public function testResetAfterRequestCallsAllResetter(): void
    {
        $a = new class implements RuntimeResetterInterface {
            public int $count = 0;

            public function reset(RuntimeResetReport $report): void
            {
                $this->count++;
            }
        };
        $b = new class implements RuntimeResetterInterface {
            public int $count = 0;

            public function reset(RuntimeResetReport $report): void
            {
                $this->count++;
            }
        };

        $registry = new RuntimeResetRegistry([$a, $b]);
        $svc = new RuntimeSuperchargerService($registry, new NullLogger());

        $svc->resetAfterRequest();
        $svc->resetAfterRequest();

        self::assertSame(2, $a->count);
        self::assertSame(2, $b->count);
    }

    public function testResetAfterRequestDoesNotStopOnFailure(): void
    {
        $ok = new class implements RuntimeResetterInterface {
            public int $count = 0;

            public function reset(RuntimeResetReport $report): void
            {
                $this->count++;
            }
        };
        $bad = new class implements RuntimeResetterInterface {
            public function reset(RuntimeResetReport $report): void
            {
                throw new \RuntimeException('boom');
            }
        };

        $registry = new RuntimeResetRegistry([$bad, $ok]);
        $svc = new RuntimeSuperchargerService($registry, new NullLogger());

        $svc->resetAfterRequest();

        self::assertSame(1, $ok->count);
    }
}
