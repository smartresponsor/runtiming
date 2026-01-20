<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infra\Runtime;


use Symfony\Contracts\Service\ResetInterface;

final class RuntimeResetterRegistry
{
    /** @var iterable<ResetInterface> */
    private iterable $resetter;

    /**
     * @param iterable<ResetInterface> $resetter Tagged services "runtime.reset"
     */
    public function __construct(iterable $resetter)
    {
        $this->resetter = $resetter;
    }

    /**
     * @return iterable<ResetInterface>
     */
    public function all(): iterable
    {
        return $this->resetter;
    }
}
