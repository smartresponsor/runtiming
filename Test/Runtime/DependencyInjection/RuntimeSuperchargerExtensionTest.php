<?php
declare(strict_types=1);



namespace App\Test\Runtime\DependencyInjection;

use App\Runtime\DependencyInjection\RuntimeSuperchargerExtension;
use App\Runtime\RuntimeSuperchargerContract;
use App\Service\Runtime\RuntimePrometheusExporter;
use App\Service\Runtime\RuntimeStatusProvider;
use App\Service\Runtime\RuntimeTelemetryAggregate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *
 */
final class RuntimeSuperchargerExtensionTest extends TestCase
{
    /**
     * @return void
     */
    public function testContainerCompileLoadsServiceConfig(): void
    {
        $container = new ContainerBuilder();

        $extension = new RuntimeSuperchargerExtension();
        $container->registerExtension($extension);

        $telemetryDir = sys_get_temp_dir() . '/runtime-supercharger-test-telemetry';

        $container->loadFromExtension($extension->getAlias(), [
            'telemetry' => [
                'dir' => $telemetryDir,
            ],
            'endpoint' => [
                'metrics' => true,
                'status' => true,
            ],
            'worker' => [
                'lifecycle' => [
                    'enabled' => false,
                ],
                'reset' => [
                    'enabled' => false,
                ],
            ],
        ]);

        $container->compile();

        self::assertTrue($container->hasParameter(RuntimeSuperchargerContract::PARAM_TELEMETRY_DIR));
        self::assertSame($telemetryDir, (string) $container->getParameter(RuntimeSuperchargerContract::PARAM_TELEMETRY_DIR));

        self::assertTrue($container->has(RuntimeTelemetryAggregate::class));
        self::assertTrue($container->has(RuntimePrometheusExporter::class));
        self::assertTrue($container->has(RuntimeStatusProvider::class));
    }
}
