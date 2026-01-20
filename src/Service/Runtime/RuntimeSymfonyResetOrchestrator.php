<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeKernelResetInterface;
use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;

final class RuntimeSymfonyResetOrchestrator
{
    private ?RuntimeKernelResetInterface $kernelReset;
    private RuntimeResetRegistryInterface $resetRegistry;
    private bool $gcCollect;

    public function __construct(
        RuntimeResetRegistryInterface $resetRegistry,
        ?RuntimeKernelResetInterface $kernelReset = null,
        bool $gcCollect = true,
    ) {
        $this->resetRegistry = $resetRegistry;
        $this->kernelReset = $kernelReset;
        $this->gcCollect = $gcCollect;
    }

    public function afterResponse(): RuntimeSymfonyResetReport
    {
        $t0 = microtime(true);

        $kernelAttempted = false;
        $kernelMs = 0.0;
        if ($this->kernelReset !== null) {
            $kernelAttempted = true;
            $k0 = microtime(true);
            $this->kernelReset->reset();
            $kernelMs = (microtime(true) - $k0) * 1000.0;
        }

        $domain = $this->resetRegistry->resetAfter();

        $gcAttempted = false;
        $gcMs = 0.0;
        if ($this->gcCollect) {
            $gcAttempted = true;
            $g0 = microtime(true);
            gc_collect_cycles();
            $gcMs = (microtime(true) - $g0) * 1000.0;
        }

        $totalMs = (microtime(true) - $t0) * 1000.0;

        return new RuntimeSymfonyResetReport(
            kernelResetAttempted: $kernelAttempted,
            kernelResetMs: $kernelMs,
            domainReset: $domain,
            gcCollectAttempted: $gcAttempted,
            gcCollectMs: $gcMs,
            totalMs: $totalMs,
        );
    }
}
