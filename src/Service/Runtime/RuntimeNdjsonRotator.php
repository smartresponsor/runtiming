<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

final class RuntimeNdjsonRotator
{
    public function rotateIfNeed(string $path, int $maxBytes, int $maxKeep): void
    {
        if ($maxBytes <= 0) {
            return;
        }

        if (!is_file($path)) {
            return;
        }

        clearstatcache(true, $path);
        $size = filesize($path);
        if (!is_int($size) || $size < $maxBytes) {
            return;
        }

        $dir = dirname($path);
        $base = basename($path, '.ndjson');

        $stamp = gmdate('Ymd\\THis\\Z');
        $rotated = $dir . DIRECTORY_SEPARATOR . $base . '-' . $stamp . '.ndjson';

        @rename($path, $rotated);

        $this->trim($dir, $base, $maxKeep);
    }

    private function trim(string $dir, string $base, int $maxKeep): void
    {
        if ($maxKeep <= 0) {
            return;
        }

        $pattern = $dir . DIRECTORY_SEPARATOR . $base . '-*.ndjson';
        $list = glob($pattern);
        if (!is_array($list) || count($list) <= $maxKeep) {
            return;
        }

        sort($list);
        $extra = array_slice($list, 0, max(0, count($list) - $maxKeep));
        foreach ($extra as $p) {
            if (is_string($p)) {
                @unlink($p);
            }
        }
    }
}
