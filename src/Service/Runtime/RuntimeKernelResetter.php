<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetReport;
use App\ServiceInterface\Runtime\RuntimeResetterInterface;

final class RuntimeKernelResetter implements RuntimeResetterInterface
{
    private string $enabledRaw;

    /**
     * @var object|null
     */
    private $servicesResetter;

    public function __construct(string $enabled, ?object $servicesResetter)
    {
        $this->enabledRaw = $enabled;
        $this->servicesResetter = $servicesResetter;
    }

    public function reset(RuntimeResetReport $report): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (!is_object($this->servicesResetter)) {
            return;
        }

        if (!method_exists($this->servicesResetter, 'reset')) {
            return;
        }

        try {
            $this->servicesResetter->reset();
        } catch (\Throwable $e) {
            $report->addError('kernel: ' . $e->getMessage());
        }
    }

    private function isEnabled(): bool
    {
        $v = strtolower(trim($this->enabledRaw));
        if ($v === '' || $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on') {
            return true;
        }
        if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
            return false;
        }
        return true;
    }
}
