<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeEventSinkInterface;
use RuntimeException;

final class RuntimeEventFileSink implements RuntimeEventSinkInterface
{
    private string $path;
    private int $maxSizeMb;

    public function __construct(string $path, int $maxSizeMb = 16)
    {
        $this->path = $path;
        $this->maxSizeMb = max(1, $maxSizeMb);
    }

    public function emit(string $type, array $payload): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException('Failed to create dir: ' . $dir);
            }
        }

        $this->rotateIfNeeded();

        $line = json_encode([
            'ts' => gmdate('c') . 'Z',
            'type' => $type,
        ] + $payload, JSON_UNESCAPED_SLASHES);

        if ($line === false) {
            return;
        }

        @file_put_contents($this->path, $line . "\n", FILE_APPEND);
    }

    private function rotateIfNeeded(): void
    {
        if (!is_file($this->path)) {
            return;
        }

        $maxByte = $this->maxSizeMb * 1024 * 1024;
        $size = @filesize($this->path);
        if (!is_int($size) || $size < $maxByte) {
            return;
        }

        $stamp = gmdate('Ymd\THis\Z');
        $rot = preg_replace('/\.ndjson$/', '-' . $stamp . '.ndjson', $this->path);
        if (!is_string($rot) || $rot === $this->path) {
            $rot = $this->path . '-' . $stamp;
        }

        @rename($this->path, $rot);
    }
}
