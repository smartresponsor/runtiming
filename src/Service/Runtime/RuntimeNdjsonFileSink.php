<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeNdjsonSinkInterface;
use Throwable;

final class RuntimeNdjsonFileSink implements RuntimeNdjsonSinkInterface
{
    private string $path;
    private int $maxBytes;
    private int $maxKeep;
    private RuntimeNdjsonRotator $rotator;

    public function __construct(string $path, int $maxBytes = 10485760, int $maxKeep = 20, ?RuntimeNdjsonRotator $rotator = null)
    {
        $this->path = $path;
        $this->maxBytes = max(0, $maxBytes);
        $this->maxKeep = max(0, $maxKeep);
        $this->rotator = $rotator ?? new RuntimeNdjsonRotator();
    }

    /** @param array<string,mixed> $event */
    public function emit(array $event): void
    {
        try {
            $dir = dirname($this->path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $this->rotator->rotateIfNeed($this->path, $this->maxBytes, $this->maxKeep);

            $line = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (!is_string($line)) {
                return;
            }

            @file_put_contents($this->path, $line . "\n", FILE_APPEND | LOCK_EX);
        } catch (Throwable $e) {
            // best-effort by design
        }
    }
}
