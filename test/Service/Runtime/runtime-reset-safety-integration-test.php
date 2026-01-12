<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Test\Service\Runtime;

require_once __DIR__ . '/../../Fixture/runtime-state-safety-fixture.php';

use App\Infra\Runtime\RuntimeResetterRegistry;
use App\Service\Runtime\RuntimeSuperchargerService;
use App\Test\Fixture\RuntimeStateSafetyFixture;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class RuntimeResetSafetyIntegrationTest extends TestCase
{
    public function testResetSafetyAcrossRequest(): void
    {
        $probe = RuntimeStateSafetyFixture::probe();
        $resetter = RuntimeStateSafetyFixture::resetter($probe);
        $registry = new RuntimeResetterRegistry([$resetter]);
        $service = new RuntimeSuperchargerService($registry, new NullLogger());

        foreach (RuntimeStateSafetyFixture::SEED as $seed) {
            $probe->store($seed['requestId'], $seed['value']);
            $beforeState = $probe->state();
            self::assertSame($seed['requestId'], $beforeState['requestId']);
            self::assertSame($seed['value'], $beforeState['value']);

            $service->resetAfterRequest();

            $afterState = $probe->state();
            self::assertNull($afterState['requestId']);
            self::assertNull($afterState['value']);
        }
    }
}
