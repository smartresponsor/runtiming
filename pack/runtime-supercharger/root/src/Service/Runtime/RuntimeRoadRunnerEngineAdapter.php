<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeEngineAdapterInterface;

final class RuntimeRoadRunnerEngineAdapter extends RuntimeEngineWrapper
{
    private RuntimeEngineAdapterInterface $adapter;

    public function __construct(RuntimeEngineAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param array<string,string> $header
     */
    public function afterResponse(?object $decision, array $header = []): void
    {
        // RoadRunner: exit after response to let rr restart worker.
        $action = $this->adapter->plan($decision, $header);
        $this->apply($action);
    }
}
