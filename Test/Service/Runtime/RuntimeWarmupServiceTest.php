<?php
declare(strict_types=1);

namespace App\Test\Service\Runtime;


use App\Infra\Runtime\RuntimeWarmerRegistry;
use App\Service\Runtime\RuntimeWarmupService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use App\RuntimeInterface\RuntimeWarmerInterface;

final class RuntimeWarmupServiceTest extends TestCase
{
    public function testWarmupRunsOnlyOnce(): void
    {
        $a = new class implements RuntimeWarmerInterface {
            public int $count = 0;
            public function warm(): void { $this->count++; }
        };
        $b = new class implements RuntimeWarmerInterface {
            public int $count = 0;
            public function warm(): void { $this->count++; }
        };

        $registry = new RuntimeWarmerRegistry([$a, $b]);
        $svc = new RuntimeWarmupService($registry, new NullLogger());

        $svc->warmupOnBoot();
        $svc->warmupOnBoot();

        self::assertSame(1, $a->count);
        self::assertSame(1, $b->count);
    }
}
