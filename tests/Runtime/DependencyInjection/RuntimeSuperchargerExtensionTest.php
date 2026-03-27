<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Test\Runtime\DependencyInjection;

use App\Contract\RuntimeSuperchargerContract;
use App\DependencyInjection\RuntimeSuperchargerExtension;
use App\Service\Runtime\RuntimeEndpointGuard;
use App\Service\Runtime\RuntimePrometheusExporter;
use App\Service\Runtime\RuntimeStatusProvider;
use App\Service\Runtime\RuntimeTelemetryAggregate;
use App\Service\Runtime\RuntimeWorkerLifecyclePolicy;
use App\Service\Runtime\RuntimeWorkerState;
use App\Service\Runtime\RuntimeWorkerTerminator;
use App\Service\Runtime\RuntimeResetRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RuntimeSuperchargerExtensionTest extends TestCase
{
    public function testContainerCompileLoadsServiceConfig(): void
    {
        $telemetryDir = sys_get_temp_dir() . '/runtime-supercharger-test-telemetry';
        $container = $this->compileWithConfig([
            'telemetry' => ['dir' => $telemetryDir],
            'endpoint' => ['metrics' => true, 'status' => true],
            'worker' => [
                'lifecycle' => ['enabled' => false],
                'reset' => ['enabled' => false],
            ],
        ]);

        self::assertTrue($container->hasParameter(RuntimeSuperchargerContract::PARAM_TELEMETRY_DIR));
        self::assertSame($telemetryDir, (string) $container->getParameter(RuntimeSuperchargerContract::PARAM_TELEMETRY_DIR));

        self::assertTrue($this->containsService($container, RuntimeTelemetryAggregate::class));
        self::assertTrue($this->containsService($container, RuntimePrometheusExporter::class));
        self::assertTrue($this->containsService($container, RuntimeStatusProvider::class));
    }

    public function testEndpointServicesAreSkippedWhenBothTogglesAreDisabled(): void
    {
        $container = $this->compileWithConfig([
            'endpoint' => [
                'metrics' => false,
                'status' => false,
            ],
        ]);

        self::assertFalse($this->containsService($container, RuntimeEndpointGuard::class));
    }

    public function testWorkerLifecycleAndResetServicesFollowToggleBranches(): void
    {
        $container = $this->compileWithConfig([
            'worker' => [
                'lifecycle' => ['enabled' => false],
                'reset' => ['enabled' => true],
            ],
        ]);

        self::assertFalse($this->containsService($container, RuntimeWorkerState::class));
        self::assertFalse($this->containsService($container, RuntimeWorkerLifecyclePolicy::class));
        self::assertFalse($this->containsService($container, RuntimeWorkerTerminator::class));
        self::assertTrue($this->containsService($container, RuntimeResetRegistry::class));
    }

    public function testWorkerParameterOverridesSupportCoercionPaths(): void
    {
        $container = $this->compileWithConfig([
            'worker' => [
                'lifecycle' => [
                    'enabled' => '0',
                    'max_request' => '111',
                    'max_memory_mb' => '222',
                    'max_uptime_second' => '333',
                    'drain_second' => '444',
                ],
                'reset' => [
                    'enabled' => '1',
                    'kernel' => '0',
                    'doctrine' => '1',
                ],
            ],
        ]);

        self::assertSame('0', (string) $container->getParameter('runtime_supercharger_worker_lifecycle_enabled'));
        self::assertSame('111', (string) $container->getParameter('runtime_supercharger_worker_max_request'));
        self::assertSame('222', (string) $container->getParameter('runtime_supercharger_worker_max_memory_mb'));
        self::assertSame('333', (string) $container->getParameter('runtime_supercharger_worker_max_uptime_second'));
        self::assertSame('444', (string) $container->getParameter('runtime_supercharger_worker_drain_second'));

        self::assertSame('1', (string) $container->getParameter('runtime_supercharger_worker_reset_enabled'));
        self::assertSame('0', (string) $container->getParameter('runtime_supercharger_worker_reset_kernel'));
        self::assertSame('1', (string) $container->getParameter('runtime_supercharger_worker_reset_doctrine'));
    }

    /** @param array<string, mixed> $config */
    private function compileWithConfig(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new RuntimeSuperchargerExtension();

        $container->registerExtension($extension);
        $container->loadFromExtension($extension->getAlias(), $config);
        $container->compile();

        return $container;
    }

    private function containsService(ContainerBuilder $container, string $serviceId): bool
    {
        return $container->has($serviceId)
            || $container->hasDefinition($serviceId)
            || $container->hasAlias($serviceId)
            || array_key_exists($serviceId, $container->getRemovedIds());
    }
}
