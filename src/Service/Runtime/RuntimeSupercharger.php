<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerInterface;
use Throwable;

final class RuntimeSupercharger implements RuntimeSuperchargerInterface
{
    private RuntimeResetRegistryInterface $registry;

    /** @var object|null */
    private ?object $servicesResetter;

    private bool $gcEnable;

    public function __construct(RuntimeResetRegistryInterface $registry, ?object $servicesResetter = null, bool $gcEnable = false)
    {
        $this->registry = $registry;
        $this->servicesResetter = $servicesResetter;
        $this->gcEnable = $gcEnable;
    }

    public function beforeRequest(): void
    {
        // intentionally empty by default
    }

    public function afterResponse(int $statusCode): void
    {
        $this->resetFramework();
        $this->registry->resetAll();

        if ($this->gcEnable) {
            $this->gcCompact();
        }
    }

    private function resetFramework(): void
    {
        if ($this->servicesResetter === null) {
            return;
        }

        if (!method_exists($this->servicesResetter, 'reset')) {
            return;
        }

        try {
            $this->servicesResetter->reset();
        } catch (Throwable $e) {
            // swallow by design: domain reset is still valuable
        }
    }

    private function gcCompact(): void
    {
        try {
            if (function_exists('gc_collect_cycles')) {
                @gc_collect_cycles();
            }
            if (function_exists('gc_mem_caches')) {
                @gc_mem_caches();
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
}
