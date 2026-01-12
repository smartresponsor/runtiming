<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetReport;
use App\ServiceInterface\Runtime\RuntimeResetterInterface;

final class RuntimeDoctrineResetter implements RuntimeResetterInterface
{
    private string $enabledRaw;

    /**
     * @var object|null
     */
    private $doctrine;

    public function __construct(string $enabled, ?object $doctrine)
    {
        $this->enabledRaw = $enabled;
        $this->doctrine = $doctrine;
    }

    public function reset(RuntimeResetReport $report): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (!is_object($this->doctrine)) {
            return;
        }

        // Best-effort: support DoctrineBundle Registry or Persistence ManagerRegistry.
        try {
            if (method_exists($this->doctrine, 'getManagerNames') && method_exists($this->doctrine, 'resetManager')) {
                /** @var mixed $names */
                $names = $this->doctrine->getManagerNames();
                if (is_array($names)) {
                    foreach (array_keys($names) as $name) {
                        if (!is_string($name)) {
                            continue;
                        }
                        try {
                            $this->doctrine->resetManager($name);
                        } catch (\Throwable $e) {
                            $report->addError('doctrine.resetManager ' . $name . ': ' . $e->getMessage());
                        }
                    }
                    return;
                }
            }

            if (method_exists($this->doctrine, 'getManagers')) {
                /** @var mixed $list */
                $list = $this->doctrine->getManagers();
                if (is_array($list)) {
                    foreach ($list as $name => $em) {
                        if (!is_object($em)) {
                            continue;
                        }
                        $this->resetEntityManager($em, $report, is_string($name) ? $name : 'default');
                    }
                    return;
                }
            }

            if (method_exists($this->doctrine, 'getManager')) {
                $em = $this->doctrine->getManager();
                if (is_object($em)) {
                    $this->resetEntityManager($em, $report, 'default');
                }
            }
        } catch (\Throwable $e) {
            $report->addError('doctrine: ' . $e->getMessage());
        }
    }

    private function resetEntityManager(object $em, RuntimeResetReport $report, string $name): void
    {
        try {
            if (method_exists($em, 'clear')) {
                $em->clear();
            }
            if (method_exists($em, 'getConnection')) {
                $conn = $em->getConnection();
                if (is_object($conn) && method_exists($conn, 'close')) {
                    $conn->close();
                }
            }
            if (method_exists($em, 'close')) {
                $em->close();
            }
        } catch (\Throwable $e) {
            $report->addError('doctrine.em ' . $name . ': ' . $e->getMessage());
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
