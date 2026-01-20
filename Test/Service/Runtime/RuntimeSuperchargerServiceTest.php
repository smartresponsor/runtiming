<?php
declare(strict_types=1);

namespace App\Test\Service\Runtime;


use App\Infra\Runtime\RuntimeResetterRegistry;
use App\Service\Runtime\RuntimeSuperchargerService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Contracts\Service\ResetInterface;

final class RuntimeSuperchargerServiceTest extends TestCase
{
    public function testResetAfterRequestCallsAllResetter(): void
    {
        $a = new class implements ResetInterface {
            public int $count = 0;
            public function reset(): void { $this->count++; }
        };
        $b = new class implements ResetInterface {
            public int $count = 0;
            public function reset(): void { $this->count++; }
        };

        $registry = new RuntimeResetterRegistry([$a, $b]);
        $svc = new RuntimeSuperchargerService($registry, new NullLogger());

        $svc->resetAfterRequest();
        $svc->resetAfterRequest();

        self::assertSame(2, $a->count);
        self::assertSame(2, $b->count);
    }

    public function testResetAfterRequestDoesNotStopOnFailure(): void
    {
        $ok = new class implements ResetInterface {
            public int $count = 0;
            public function reset(): void { $this->count++; }
        };
        $bad = new class implements ResetInterface {
            public function reset(): void { throw new \RuntimeException('boom'); }
        };

        $registry = new RuntimeResetterRegistry([$bad, $ok]);
        $svc = new RuntimeSuperchargerService($registry, new NullLogger());

        $svc->resetAfterRequest();

        self::assertSame(1, $ok->count);
    }
}
