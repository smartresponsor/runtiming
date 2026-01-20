<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerLifecyclePolicyInterface
{
    public function decide(RuntimeWorkerStateInterface $state): RuntimeWorkerLifecycleDecision;
}
