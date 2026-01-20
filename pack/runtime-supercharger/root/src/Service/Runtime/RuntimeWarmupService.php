<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service\Runtime;

use App\Infra\Runtime\RuntimeWarmerRegistry;
use App\ServiceInterface\Runtime\RuntimeWarmupServiceInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class RuntimeWarmupService implements RuntimeWarmupServiceInterface
{
    private RuntimeWarmerRegistry $warmerRegistry;
    private LoggerInterface $logger;
    private bool $hasWarmed = false;

    public function __construct(RuntimeWarmerRegistry $warmerRegistry, LoggerInterface $logger)
    {
        $this->warmerRegistry = $warmerRegistry;
        $this->logger = $logger;
    }

    public function warmupOnBoot(): void
    {
        if ($this->hasWarmed) {
            return;
        }

        foreach ($this->warmerRegistry->all() as $warmer) {
            try {
                $warmer->warm();
            } catch (Throwable $e) {
                $this->logger->error('Runtime warmer failed', [
                    'warmer' => get_class($warmer),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->hasWarmed = true;
    }
}
