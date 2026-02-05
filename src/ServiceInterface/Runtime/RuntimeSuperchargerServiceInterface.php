<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Runtime;

/**
 * @deprecated since 1.4.0, to be removed in 1.6.0. Use RuntimeSuperchargerInterface or RuntimeResetRegistryInterface.
 */
interface RuntimeSuperchargerServiceInterface
{
    public function resetAfterRequest(): void;
}
