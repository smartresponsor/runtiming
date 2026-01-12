<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ServiceInterface\Runtime;


interface RuntimeSuperchargerServiceInterface
{
    /**
     * Reset all mutable long-lived services after a request.
     * Must be safe to call on every request boundary.
     */
    public function resetAfterRequest(): void;
}
