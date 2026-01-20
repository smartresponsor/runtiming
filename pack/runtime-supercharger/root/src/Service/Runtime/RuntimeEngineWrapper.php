<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeEngineAction;

abstract class RuntimeEngineWrapper
{
    protected function apply(RuntimeEngineAction $action): void
    {
        $type = $action->getType();
        if ($type === 'none') {
            return;
        }

        // Wrapper contract: exit so the engine restarts the worker.
        if ($type === 'gracefulExit') {
            exit(0);
        }

        if ($type === 'hardExit') {
            exit(1);
        }
    }
}
