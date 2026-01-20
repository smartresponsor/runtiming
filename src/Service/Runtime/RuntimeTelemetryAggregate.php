<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeTelemetrySnapshot;

final class RuntimeTelemetryAggregate
{
    private string $dir;
    private RuntimeTelemetryJsonCodec $codec;

    public function __construct(string $dir, RuntimeTelemetryJsonCodec $codec)
    {
        $this->dir = $dir !== '' ? $dir : 'var/runtime/telemetry';
        $this->codec = $codec;
    }

    public function aggregate(): RuntimeTelemetrySnapshot
    {
        $snap = new RuntimeTelemetrySnapshot('runtime');

        $files = $this->findFile($this->dir);
        foreach ($files as $path) {
            $raw = @file_get_contents($path);
            if (!is_string($raw) || $raw === '') {
                continue;
            }

            $one = $this->codec->decode($raw);
            if (!$one instanceof RuntimeTelemetrySnapshot) {
                continue;
            }

            if ($snap->namespace === 'runtime' && $one->namespace !== '') {
                $snap->namespace = $one->namespace;
            }

            foreach ($one->counter as $k => $v) {
                $snap->counter[$k] = ($snap->counter[$k] ?? 0) + (int) $v;
            }

            foreach ($one->gauge as $k => $v) {
                $name = $this->metricName($k);
                $prev = $snap->gauge[$k] ?? null;

                if ($prev === null) {
                    $snap->gauge[$k] = (float) $v;
                    continue;
                }

                if ($this->isAddGauge($name)) {
                    $snap->gauge[$k] = (float) $prev + (float) $v;
                    continue;
                }

                if ($this->isMinGauge($name)) {
                    $snap->gauge[$k] = min((float) $prev, (float) $v);
                    continue;
                }

                $snap->gauge[$k] = max((float) $prev, (float) $v);
            }
        }

        return $snap;
    }

    /** @return array<int,string> */
    private function findFile(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $out = [];
        $h = @opendir($dir);
        if (!is_resource($h)) {
            return [];
        }

        while (($name = readdir($h)) !== false) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            if (!str_ends_with($name, '.json')) {
                continue;
            }
            $out[] = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
        }

        closedir($h);
        sort($out);
        return $out;
    }

    private function metricName(string $key): string
    {
        $pos = strpos($key, '{');
        if ($pos === false) {
            return $key;
        }
        return substr($key, 0, $pos);
    }

    private function isAddGauge(string $name): bool
    {
        return str_ends_with($name, '_sum') || str_ends_with($name, '_count');
    }

    private function isMinGauge(string $name): bool
    {
        return $name === 'runtime_supercharger_worker_start_time_second';
    }
}
