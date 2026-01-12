<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeMetricExporterInterface;
use App\ServiceInterface\Runtime\RuntimeMetricRegistryInterface;
use Throwable;

final class RuntimePrometheusFileExporter implements RuntimeMetricExporterInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function export(RuntimeMetricRegistryInterface $registry): void
    {
        try {
            $dir = dirname($this->path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $tmp = $this->path . '.tmp';
            @file_put_contents($tmp, $registry->renderText(), LOCK_EX);
            @rename($tmp, $this->path);
        } catch (Throwable $e) {
            // best-effort by design
        }
    }
}
