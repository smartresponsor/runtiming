<?php
declare(strict_types=1);



namespace App\Runtime\DependencyInjection;

use App\Runtime\RuntimeSuperchargerContract;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class RuntimeSuperchargerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new RuntimeSuperchargerConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        // This bundle is distributed as a standalone package, where service configs live at the repo root.
        // Keep the locator stable to avoid breakage when the bundle is installed via a path repository.
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../../resource/config'));

        $loader->load('package.yaml');
        $loader->load('service-core.yaml');

        $dir = (string) ($config['telemetry']['dir'] ?? '');
        if ($dir !== '') {
            $container->setParameter(RuntimeSuperchargerContract::PARAM_TELEMETRY_DIR, $dir);
        }

        $endpoint = $config['endpoint'] ?? [];
        $metrics = (bool) ($endpoint['metrics'] ?? true);
        $status = (bool) ($endpoint['status'] ?? true);

        if ($metrics || $status) {
            $loader->load('service-endpoint.yaml');
        }

        $worker = (array) (($config['worker'] ?? []) ?: []);

        // Lifecycle services (sketch-30)
        $life = (array) (($worker['lifecycle'] ?? []) ?: []);
        $lifeEnabled = (bool) (($life['enabled'] ?? true) ?: false);
        if ($lifeEnabled) {
            $loader->load('service-worker.yaml');
        }

        // Reset services (sketch-31)
        $reset = (array) (($worker['reset'] ?? []) ?: []);
        $resetEnabled = (bool) (($reset['enabled'] ?? true) ?: false);
        if ($resetEnabled) {
            $loader->load('service-worker-reset.yaml');
        }

        // Optional parameter overrides if lifecycle is explicitly configured.
        $hasLife = false;
        foreach ($configs as $c) {
            if (isset($c['worker']) && is_array($c['worker']) && isset($c['worker']['lifecycle'])) {
                $hasLife = true;
                break;
            }
        }

        if ($hasLife) {
            $maxRequest = (int) ($life['max_request'] ?? 1000);
            $maxMemoryMb = (int) ($life['max_memory_mb'] ?? 512);
            $maxUptime = (int) ($life['max_uptime_second'] ?? 3600);
            $drainSecond = (int) ($life['drain_second'] ?? 10);

            $container->setParameter('runtime_supercharger_worker_lifecycle_enabled', $lifeEnabled ? '1' : '0');
            $container->setParameter('runtime_supercharger_worker_max_request', (string) $maxRequest);
            $container->setParameter('runtime_supercharger_worker_max_memory_mb', (string) $maxMemoryMb);
            $container->setParameter('runtime_supercharger_worker_max_uptime_second', (string) $maxUptime);
            $container->setParameter('runtime_supercharger_worker_drain_second', (string) $drainSecond);
        }

        // Optional parameter overrides if reset is explicitly configured.
        $hasReset = false;
        foreach ($configs as $c) {
            if (isset($c['worker']) && is_array($c['worker']) && isset($c['worker']['reset'])) {
                $hasReset = true;
                break;
            }
        }

        if ($hasReset) {
            $kernel = (bool) (($reset['kernel'] ?? true) ?: false);
            $doctrine = (bool) (($reset['doctrine'] ?? true) ?: false);

            $container->setParameter('runtime_supercharger_worker_reset_enabled', $resetEnabled ? '1' : '0');
            $container->setParameter('runtime_supercharger_worker_reset_kernel', $kernel ? '1' : '0');
            $container->setParameter('runtime_supercharger_worker_reset_doctrine', $doctrine ? '1' : '0');
        }
    }
}
