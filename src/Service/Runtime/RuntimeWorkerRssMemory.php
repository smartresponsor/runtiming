<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

final class RuntimeWorkerRssMemory
{
    public static function readRssMb(): int
    {
        $rssByte = self::readLinuxProcRssByte();
        if ($rssByte > 0) {
            return (int) floor($rssByte / 1024 / 1024);
        }

        // Portable fallback
        $byte = memory_get_usage(true);
        return (int) floor($byte / 1024 / 1024);
    }

    private static function readLinuxProcRssByte(): int
    {
        $path = '/proc/self/statm';
        if (!is_file($path) || !is_readable($path)) {
            return 0;
        }

        $raw = @file_get_contents($path);
        if (!is_string($raw) || $raw == '') {
            return 0;
        }

        // statm format: size resident shared text lib data dt
        $part = preg_split('/\s+/', trim($raw));
        if (!is_array($part) || count($part) < 2) {
            return 0;
        }

        $residentPage = (int) $part[1];
        if ($residentPage <= 0) {
            return 0;
        }

        // Most Linux systems use 4096. Keep it deterministic.
        $pageSize = 4096;

        return $residentPage * $pageSize;
    }
}
