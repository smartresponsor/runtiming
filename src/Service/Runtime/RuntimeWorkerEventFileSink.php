<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerEventSinkInterface;
use RuntimeException;

final class RuntimeWorkerEventFileSink implements RuntimeWorkerEventSinkInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function onDecision(RuntimeWorkerDecision $decision): void
    {
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException('Failed to create dir: ' . $dir);
            }
        }

        $line = json_encode([
            'ts' => gmdate('Y-m-d\\TH:i:s\\Z'),
            'type' => 'runtime-worker-decision',
        ] + $decision->toArray(), JSON_UNESCAPED_SLASHES);

        if ($line === false) {
            return;
        }

        @file_put_contents($this->path, $line . "\n", FILE_APPEND | LOCK_EX);
    }
}
