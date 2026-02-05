<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
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

        $loader = $this->createLoader($container);
        $worker = (array) (($config['worker'] ?? []) ?: []);
        $lifecycle = (array) (($worker['lifecycle'] ?? []) ?: []);
        $reset = (array) (($worker['reset'] ?? []) ?: []);

        $this->loadCoreServices($loader);
        $this->applyTelemetryOverride($config, $container);
        $this->loadEndpoint($config, $loader);

        $lifecycleEnabled = $this->loadLifecycle($lifecycle, $loader);
        $resetEnabled = $this->loadReset($reset, $loader);

        $this->applyLifecycleOverrides($configs, $lifecycle, $lifecycleEnabled, $container);
        $this->applyResetOverrides($configs, $reset, $resetEnabled, $container);
    }

    private function createLoader(ContainerBuilder $container): YamlFileLoader
    {
        return new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../../resource/config'));
    }

    private function loadCoreServices(YamlFileLoader $loader): void
    {
        $loader->load('package.yaml');
        $loader->load('service-core.yaml');
    }

    /** @param array<string, mixed> $config */
    private function applyTelemetryOverride(array $config, ContainerBuilder $container): void
    {
        $dir = (string) ($config['telemetry']['dir'] ?? '');
        if ($dir !== '') {
            $container->setParameter(RuntimeSuperchargerContract::PARAM_TELEMETRY_DIR, $dir);
        }
    }

    /** @param array<string, mixed> $config */
    private function loadEndpoint(array $config, YamlFileLoader $loader): void
    {
        $endpoint = $config['endpoint'] ?? [];
        $metrics = (bool) ($endpoint['metrics'] ?? true);
        $status = (bool) ($endpoint['status'] ?? true);

        if ($metrics || $status) {
            $loader->load('service-endpoint.yaml');
        }
    }

    /** @param array<string, mixed> $lifecycle */
    private function loadLifecycle(array $lifecycle, YamlFileLoader $loader): bool
    {
        $enabled = (bool) (($lifecycle['enabled'] ?? true) ?: false);
        if ($enabled) {
            $loader->load('service-worker.yaml');
        }

        return $enabled;
    }

    /** @param array<string, mixed> $reset */
    private function loadReset(array $reset, YamlFileLoader $loader): bool
    {
        $enabled = (bool) (($reset['enabled'] ?? true) ?: false);
        if ($enabled) {
            $loader->load('service-worker-reset.yaml');
        }

        return $enabled;
    }

    /** @param array<array<string, mixed>> $configs
     *  @param array<string, mixed> $lifecycle
     */
    private function applyLifecycleOverrides(array $configs, array $lifecycle, bool $enabled, ContainerBuilder $container): void
    {
        if (!$this->hasExplicitWorkerSection($configs, 'lifecycle')) {
            return;
        }

        $this->applyParameterOverride($container, 'runtime_supercharger_worker_lifecycle_enabled', $enabled ? '1' : '0');
        $this->applyParameterOverride($container, 'runtime_supercharger_worker_max_request', (string) ((int) ($lifecycle['max_request'] ?? 1000)));
        $this->applyParameterOverride($container, 'runtime_supercharger_worker_max_memory_mb', (string) ((int) ($lifecycle['max_memory_mb'] ?? 512)));
        $this->applyParameterOverride($container, 'runtime_supercharger_worker_max_uptime_second', (string) ((int) ($lifecycle['max_uptime_second'] ?? 3600)));
        $this->applyParameterOverride($container, 'runtime_supercharger_worker_drain_second', (string) ((int) ($lifecycle['drain_second'] ?? 10)));
    }

    /** @param array<array<string, mixed>> $configs
     *  @param array<string, mixed> $reset
     */
    private function applyResetOverrides(array $configs, array $reset, bool $enabled, ContainerBuilder $container): void
    {
        if (!$this->hasExplicitWorkerSection($configs, 'reset')) {
            return;
        }

        $kernel = (bool) (($reset['kernel'] ?? true) ?: false);
        $doctrine = (bool) (($reset['doctrine'] ?? true) ?: false);

        $this->applyParameterOverride($container, 'runtime_supercharger_worker_reset_enabled', $enabled ? '1' : '0');
        $this->applyParameterOverride($container, 'runtime_supercharger_worker_reset_kernel', $kernel ? '1' : '0');
        $this->applyParameterOverride($container, 'runtime_supercharger_worker_reset_doctrine', $doctrine ? '1' : '0');
    }

    /** @param array<array<string, mixed>> $configs */
    private function hasExplicitWorkerSection(array $configs, string $section): bool
    {
        foreach ($configs as $config) {
            if (isset($config['worker']) && is_array($config['worker']) && isset($config['worker'][$section])) {
                return true;
            }
        }

        return false;
    }

    private function applyParameterOverride(ContainerBuilder $container, string $name, string $value): void
    {
        $container->setParameter($name, $value);
    }
}
