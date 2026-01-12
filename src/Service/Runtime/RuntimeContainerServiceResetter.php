<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeResetterInterface;
use App\ServiceInterface\Runtime\RuntimeResetReport;

final class RuntimeContainerServiceResetter implements RuntimeResetterInterface
{
    /** @var array<int,string> */
    private array $serviceId;

    /** @var callable(RuntimeResetReport):void|null */
    private $reportHook;

    /** @param array<int,string> $serviceId */
    public function __construct(array $serviceId, ?callable $reportHook = null)
    {
        $this->serviceId = $serviceId;
        $this->reportHook = $reportHook;
    }

    public function reset(object $kernel): void
    {
        $report = new RuntimeResetReport();

        $container = $this->resolveContainer($kernel);
        if ($container === null) {
            $report->add('kernel', get_class($kernel), 'skip', true, 'no container');
            $this->emitReport($report);
            return;
        }

        foreach ($this->serviceId as $id) {
            $this->resetOne($container, $id, $report);
        }

        $this->emitReport($report);
    }

    private function emitReport(RuntimeResetReport $report): void
    {
        if ($this->reportHook !== null) {
            ($this->reportHook)($report);
        }
    }

    private function resolveContainer(object $kernel): ?object
    {
        if (!method_exists($kernel, 'getContainer')) {
            return null;
        }
        $c = $kernel->getContainer();
        return is_object($c) ? $c : null;
    }

    private function resetOne(object $container, string $id, RuntimeResetReport $report): void
    {
        if (!method_exists($container, 'has') || !method_exists($container, 'get')) {
            $report->add($id, 'container', 'skip', false, 'container has/get not available');
            return;
        }

        try {
            $has = $container->has($id);
        } catch (\Throwable $e) {
            $report->add($id, 'container', 'skip', false, 'container has failed: ' . $e->getMessage());
            return;
        }

        if (!$has) {
            $report->add($id, 'service', 'skip', true, 'not found');
            return;
        }

        try {
            $svc = $container->get($id);
        } catch (\Throwable $e) {
            $report->add($id, 'service', 'get', false, $e->getMessage());
            return;
        }

        if (!is_object($svc)) {
            $report->add($id, 'service', 'skip', true, 'not object');
            return;
        }

        $type = get_class($svc);

        // 1) Symfony ResetInterface or reset() method.
        if ($this->tryResetMethod($svc)) {
            $report->add($id, $type, 'reset', true, '');
            return;
        }

        // 2) Doctrine EntityManager: clear(), optional close().
        if ($this->tryDoctrineEntityManager($svc)) {
            $report->add($id, $type, 'doctrineEm', true, '');
            return;
        }

        // 3) Doctrine Connection: close().
        if ($this->tryDoctrineConnection($svc)) {
            $report->add($id, $type, 'doctrineConn', true, '');
            return;
        }

        // 4) Cache pool: clear().
        if ($this->tryCacheClear($svc)) {
            $report->add($id, $type, 'cacheClear', true, '');
            return;
        }

        $report->add($id, $type, 'skip', true, 'no known reset strategy');
    }

    private function tryResetMethod(object $svc): bool
    {
        if (method_exists($svc, 'reset')) {
            try {
                $svc->reset();
                return true;
            } catch (\Throwable) {
                return false;
            }
        }
        return false;
    }

    private function tryDoctrineEntityManager(object $svc): bool
    {
        if (class_exists(\Doctrine\ORM\EntityManagerInterface::class) && $svc instanceof \Doctrine\ORM\EntityManagerInterface) {
            try {
                $svc->clear();
                if (method_exists($svc, 'close')) {
                    $svc->close();
                }
                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        if (method_exists($svc, 'clear')) {
            try {
                $svc->clear();
                if (method_exists($svc, 'close')) {
                    $svc->close();
                }
                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    private function tryDoctrineConnection(object $svc): bool
    {
        if (class_exists(\Doctrine\DBAL\Connection::class) && $svc instanceof \Doctrine\DBAL\Connection) {
            if (!method_exists($svc, 'close')) {
                return false;
            }
            try {
                $svc->close();
                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    private function tryCacheClear(object $svc): bool
    {
        if (class_exists(\Psr\Cache\CacheItemPoolInterface::class) && $svc instanceof \Psr\Cache\CacheItemPoolInterface) {
            try {
                $svc->clear();
                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        if (class_exists(\Symfony\Contracts\Cache\CacheInterface::class) && $svc instanceof \Symfony\Contracts\Cache\CacheInterface) {
            if (!method_exists($svc, 'clear')) {
                return false;
            }
            try {
                $svc->clear();
                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        if (method_exists($svc, 'clear')) {
            try {
                $svc->clear();
                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }
}
