<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\RuntimeInterface;


/**
 *
 */
interface RuntimeWarmerInterface
{
    /**
     * Warmup hook executed at worker boot.
     */
    public function warm(): void;
}
