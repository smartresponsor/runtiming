<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerLifecycleDecision;
use App\ServiceInterface\Runtime\RuntimeWorkerLifecyclePolicyInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerStateInterface;

final class RuntimeWorkerLifecyclePolicy implements RuntimeWorkerLifecyclePolicyInterface
{
    private string $enabledRaw;
    private string $maxRequestRaw;
    private string $maxMemoryMbRaw;
    private string $maxUptimeSecondRaw;

    public function __construct(string $enabled, string $maxRequest, string $maxMemoryMb, string $maxUptimeSecond)
    {
        $this->enabledRaw = $enabled;
        $this->maxRequestRaw = $maxRequest;
        $this->maxMemoryMbRaw = $maxMemoryMb;
        $this->maxUptimeSecondRaw = $maxUptimeSecond;
    }

    public function decide(RuntimeWorkerStateInterface $state): RuntimeWorkerLifecycleDecision
    {
        if (!$this->isEnabled()) {
            return RuntimeWorkerLifecycleDecision::keep('disabled');
        }

        $maxRequest = max(0, (int) trim($this->maxRequestRaw));
        if ($maxRequest > 0 && $state->getRequestCount() >= $maxRequest) {
            return RuntimeWorkerLifecycleDecision::recycle('maxRequest');
        }

        $maxMemory = max(0, (int) trim($this->maxMemoryMbRaw));
        if ($maxMemory > 0 && $state->getMemoryUsageMb() >= $maxMemory) {
            return RuntimeWorkerLifecycleDecision::recycle('maxMemory');
        }

        $maxUptime = max(0, (int) trim($this->maxUptimeSecondRaw));
        if ($maxUptime > 0 && $state->getUptimeSecond() >= $maxUptime) {
            return RuntimeWorkerLifecycleDecision::recycle('maxUptime');
        }

        if ($state->isRecyclePending()) {
            return RuntimeWorkerLifecycleDecision::recycle($state->getRecycleReason() !== '' ? $state->getRecycleReason() : 'pending');
        }

        return RuntimeWorkerLifecycleDecision::keep('keep');
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
