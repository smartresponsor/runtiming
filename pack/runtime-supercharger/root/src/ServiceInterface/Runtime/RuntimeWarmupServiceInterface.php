<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Runtime;

interface RuntimeWarmupServiceInterface
{
    /**
     * Run configured warmers at worker boot.
     * Must be safe to call once per worker lifecycle.
     */
    public function warmupOnBoot(): void;
}
