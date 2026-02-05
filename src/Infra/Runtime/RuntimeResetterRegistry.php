<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infra\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeResetReport;

/**
 * @deprecated since 1.4.0, to be removed in 1.6.0. Use RuntimeResetRegistryInterface instead.
 */
final class RuntimeResetterRegistry implements RuntimeResetRegistryInterface
{
    private RuntimeResetRegistryInterface $registry;

    public function __construct(RuntimeResetRegistryInterface $registry)
    {
        $this->registry = $registry;

        if ($this->shouldTriggerDeprecation()) {
            @trigger_error(self::class . ' is deprecated since 1.4.0 and will be removed in 1.6.0. Use RuntimeResetRegistryInterface.', E_USER_DEPRECATED);
        }
    }

    public function resetAll(): RuntimeResetReport
    {
        return $this->registry->resetAll();
    }

    private function shouldTriggerDeprecation(): bool
    {
        $env = (string) ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: '');

        return $env === 'dev' || $env === 'test';
    }
}
