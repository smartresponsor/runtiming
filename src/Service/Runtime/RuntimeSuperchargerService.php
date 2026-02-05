<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerServiceInterface;
use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated since 1.4.0, to be removed in 1.6.0. Use RuntimeSupercharger or RuntimeResetRegistryInterface directly.
 */
final class RuntimeSuperchargerService implements RuntimeSuperchargerServiceInterface
{
    private RuntimeResetRegistryInterface $registry;
    private LoggerInterface $logger;

    public function __construct(RuntimeResetRegistryInterface $registry, LoggerInterface $logger)
    {
        $this->registry = $registry;
        $this->logger = $logger;

        if ($this->shouldTriggerDeprecation()) {
            @trigger_error(self::class . ' is deprecated since 1.4.0 and will be removed in 1.6.0. Use RuntimeSupercharger or RuntimeResetRegistryInterface.', E_USER_DEPRECATED);
        }
    }

    public function resetAfterRequest(): void
    {
        $report = $this->registry->resetAll();

        foreach ($report->error as $errorMessage) {
            $this->logger->error('Runtime resetter failed', [
                'error' => $errorMessage,
            ]);
        }
    }

    private function shouldTriggerDeprecation(): bool
    {
        $env = (string) ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: '');

        return $env === 'dev' || $env === 'test';
    }
}
