<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerServiceInterface;
use App\Infra\Runtime\RuntimeResetterRegistry;
use Psr\Log\LoggerInterface;
use Throwable;

final class RuntimeSuperchargerService implements RuntimeSuperchargerServiceInterface
{
    private RuntimeResetterRegistry $resetterRegistry;
    private LoggerInterface $logger;

    public function __construct(RuntimeResetterRegistry $resetterRegistry, LoggerInterface $logger)
    {
        $this->resetterRegistry = $resetterRegistry;
        $this->logger = $logger;
    }

    public function resetAfterRequest(): void
    {
        foreach ($this->resetterRegistry->all() as $resetter) {
            try {
                $resetter->reset();
            } catch (Throwable $e) {
                // Do not stop the request-cycle. Record and continue.
                $this->logger->error('Runtime resetter failed', [
                    'resetter' => get_class($resetter),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
