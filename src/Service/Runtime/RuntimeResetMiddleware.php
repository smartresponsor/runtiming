<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;

final class RuntimeResetMiddleware
{
    private RuntimeResetRegistryInterface $registry;
    private bool $resetBefore;
    private bool $resetAfter;

    public function __construct(RuntimeResetRegistryInterface $registry, bool $resetBefore = true, bool $resetAfter = true)
    {
        $this->registry = $registry;
        $this->resetBefore = $resetBefore;
        $this->resetAfter = $resetAfter;
    }

    /**
     * @template T
     * @param callable():T $next
     * @return T
     */
    public function call(callable $next): mixed
    {
        if ($this->resetBefore) {
            $this->registry->resetAll();
        }

        try {
            return $next();
        } finally {
            if ($this->resetAfter) {
                $this->registry->resetAll();
            }
        }
    }
}
