<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infra\Runtime;


use App\InfraInterface\Runtime\RuntimeWarmerInterface;

final class RuntimeWarmerRegistry
{
    /** @var iterable<RuntimeWarmerInterface> */
    private iterable $warmer;

    /**
     * @param iterable<RuntimeWarmerInterface> $warmer Tagged services "runtime.warm"
     */
    public function __construct(iterable $warmer)
    {
        $this->warmer = $warmer;
    }

    /**
     * @return iterable<RuntimeWarmerInterface>
     */
    public function all(): iterable
    {
        return $this->warmer;
    }
}
