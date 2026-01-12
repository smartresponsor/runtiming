<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Infra\Runtime;

use App\InfraInterface\Runtime\RuntimeSuperchargerConfigProviderInterface;
use App\Service\Runtime\RuntimeSuperchargerConfig;
use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigInterface;

final class RuntimeSuperchargerEnvConfigProvider implements RuntimeSuperchargerConfigProviderInterface
{
    public function getConfig(): RuntimeSuperchargerConfigInterface
    {
        $d = RuntimeSuperchargerConfig::default();

        $a = [
            'beforeEnable' => $this->envBool('RUNTIME_SUPERCHARGER_BEFORE_ENABLE', $d->getBeforeEnable()),
            'afterEnable' => $this->envBool('RUNTIME_SUPERCHARGER_AFTER_ENABLE', $d->getAfterEnable()),
            'gcEnable' => $this->envBool('RUNTIME_SUPERCHARGER_GC_ENABLE', $d->getGcEnable()),

            'maxRequest' => $this->envInt('RUNTIME_SUPERCHARGER_MAX_REQUEST', $d->getMaxRequest()),
            'maxUptimeSec' => $this->envInt('RUNTIME_SUPERCHARGER_MAX_UPTIME_SEC', $d->getMaxUptimeSec()),
            'softMemoryMb' => $this->envInt('RUNTIME_SUPERCHARGER_SOFT_MEMORY_MB', $d->getSoftMemoryMb()),
            'maxMemoryMb' => $this->envInt('RUNTIME_SUPERCHARGER_MAX_MEMORY_MB', $d->getMaxMemoryMb()),

            'feedPath' => $this->envStr('RUNTIME_SUPERCHARGER_FEED_PATH', $d->getFeedPath()),
            'feedMaxBytes' => $this->envInt('RUNTIME_SUPERCHARGER_FEED_MAX_BYTES', $d->getFeedMaxBytes()),
            'feedMaxKeep' => $this->envInt('RUNTIME_SUPERCHARGER_FEED_MAX_KEEP', $d->getFeedMaxKeep()),
        ];

        return RuntimeSuperchargerConfig::fromArray($a);
    }

    private function envStr(string $key, string $default): string
    {
        $v = getenv($key);
        if (!is_string($v) || $v === '') {
            return $default;
        }
        return $v;
    }

    private function envInt(string $key, int $default): int
    {
        $v = getenv($key);
        if (!is_string($v) || trim($v) === '') {
            return $default;
        }
        $n = filter_var($v, FILTER_VALIDATE_INT);
        if ($n === false) {
            return $default;
        }
        return (int) $n;
    }

    private function envBool(string $key, bool $default): bool
    {
        $v = getenv($key);
        if (!is_string($v) || trim($v) === '') {
            return $default;
        }

        $vv = strtolower(trim($v));
        if (in_array($vv, ['1','true','yes','on'], true)) {
            return true;
        }
        if (in_array($vv, ['0','false','no','off'], true)) {
            return false;
        }
        return $default;
    }
}
