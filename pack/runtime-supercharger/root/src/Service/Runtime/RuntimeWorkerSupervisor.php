<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerDecision;
use App\ServiceInterface\Runtime\RuntimeWorkerLimitInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerStatInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerSupervisorInterface;

final class RuntimeWorkerSupervisor implements RuntimeWorkerSupervisorInterface
{
    private RuntimeWorkerLimitInterface $limit;
    private RuntimeWorkerStatInterface $stat;

    public function __construct(RuntimeWorkerLimitInterface $limit, RuntimeWorkerStatInterface $stat)
    {
        $this->limit = $limit;
        $this->stat = $stat;
    }

    public function afterRequest(int $statusCode): RuntimeWorkerDecision
    {
        $count = $this->stat->incRequest();
        $uptime = $this->stat->getUptimeSec();
        $rss = $this->stat->getRssMemoryMb();

        $maxMemory = $this->limit->getMaxMemoryMb();
        $softMemory = $this->limit->getSoftMemoryMb();
        $maxRequest = $this->limit->getMaxRequest();
        $maxUptime = $this->limit->getMaxUptimeSec();

        $reason = 'ok';
        $should = false;

        if ($rss >= $maxMemory) {
            $reason = 'maxMemory';
            $should = true;
        } elseif ($count >= $maxRequest) {
            $reason = 'maxRequest';
            $should = true;
        } elseif ($uptime >= $maxUptime) {
            $reason = 'maxUptime';
            $should = true;
        } elseif ($softMemory > 0 && $rss >= $softMemory) {
            $reason = 'softMemory';
            $should = true;
        }

        return new RuntimeWorkerDecision($should, $reason, $count, $uptime, $rss, $statusCode);
    }

    public function reset(): void
    {
        $this->stat->reset();
    }
}
