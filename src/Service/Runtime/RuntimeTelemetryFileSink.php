<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeTelemetrySinkConfig;
use App\ServiceInterface\Runtime\RuntimeTelemetrySinkInterface;
use App\ServiceInterface\Runtime\RuntimeTelemetrySnapshot;

final class RuntimeTelemetryFileSink implements RuntimeTelemetrySinkInterface
{
    private RuntimeTelemetrySinkConfig $config;
    private RuntimeTelemetryJsonCodec $codec;

    /** @var callable():float */
    private $now;

    private float $lastFlushAt = 0.0;

    /**
     * @param callable():float|null $now
     */
    public function __construct(RuntimeTelemetrySinkConfig $config, RuntimeTelemetryJsonCodec $codec, ?callable $now = null)
    {
        $this->config = $config;
        $this->codec = $codec;
        $this->now = $now ?? static fn (): float => microtime(true);
    }

    public function flush(RuntimeTelemetrySnapshot $snapshot): void
    {
        $t = ($this->now)();

        if ($this->config->flushIntervalSec > 0.0 && $this->lastFlushAt > 0.0) {
            if (($t - $this->lastFlushAt) < $this->config->flushIntervalSec) {
                return;
            }
        }

        $dir = $this->config->dir;
        if ($dir === '') {
            $dir = 'var/runtime/telemetry';
        }

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $final = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->safeName($this->config->workerId) . '.json';
        $tmp = $final . '.tmp.' . (string) getmypid() . '.' . (string) random_int(1000, 9999);

        $json = $this->codec->encode($snapshot);

        $ok = @file_put_contents($tmp, $json, LOCK_EX);
        if ($ok === false) {
            @unlink($tmp);
            return;
        }

        $this->safeReplace($tmp, $final);
        $this->lastFlushAt = $t;
    }

    private function safeName(string $id): string
    {
        $id = preg_replace('/[^a-zA-Z0-9._-]/', '_', $id) ?: 'worker';
        return $id;
    }

    private function safeReplace(string $tmp, string $final): void
    {
        if (@rename($tmp, $final)) {
            return;
        }

        // Windows may not allow rename over existing file.
        @unlink($final);
        @rename($tmp, $final);
    }
}
