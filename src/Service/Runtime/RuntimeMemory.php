<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

final class RuntimeMemory
{
    public static function getRssMemoryMb(): int
    {
        $rss = self::rssByte();
        if ($rss <= 0) {
            $rss = (int) memory_get_usage(true);
        }
        $mb = (int) round($rss / 1024 / 1024);
        return max(0, $mb);
    }

    private static function rssByte(): int
    {
        $rss = 0;

        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/self/statm')) {
            $data = @file_get_contents('/proc/self/statm');
            if ($data !== false) {
                $parts = preg_split('/\s+/', trim($data)) ?: [];
                if (isset($parts[1])) {
                    $pages = (int) $parts[1];
                    $pageSize = 4096;
                    $rss = $pages * $pageSize;
                }
            }
        }

        return $rss;
    }
}
