<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetRegistryInterface;
use App\ServiceInterface\Runtime\RuntimeSuperchargerMetricEmitterInterface;
use Throwable;

final class RuntimeResetRegistryMetricDecorator implements RuntimeResetRegistryInterface
{
    private RuntimeResetRegistryInterface $inner;
    private RuntimeSuperchargerMetricEmitterInterface $emitter;

    public function __construct(RuntimeResetRegistryInterface $inner, RuntimeSuperchargerMetricEmitterInterface $emitter)
    {
        $this->inner = $inner;
        $this->emitter = $emitter;
    }

    public function addResetter(object $resetter): void
    {
        $this->inner->addResetter($resetter);
    }

    public function resetAll(): object
    {
        $ok = true;
        $t0 = microtime(true);
        try {
            $report = $this->inner->resetAll();
        } catch (Throwable $e) {
            $ok = false;
            throw $e;
        } finally {
            $dt = microtime(true) - $t0;
            if (isset($report) && is_object($report)) {
                $this->emitter->onReset($report, (float) $dt, $ok);
            }
        }

        return $report;
    }
}
