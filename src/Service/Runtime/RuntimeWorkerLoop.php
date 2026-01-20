<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeEngineAdapterInterface;
use App\ServiceInterface\Runtime\RuntimeEngineAction;
use App\ServiceInterface\Runtime\RuntimeRunnerInterface;
use App\ServiceInterface\Runtime\RunnerRequest;
use App\ServiceInterface\Runtime\RunnerResponse;

final class RuntimeWorkerLoop
{
    private RuntimeRunnerInterface $runner;
    private RuntimeEngineAdapterInterface $engineAdapter;
    private int $maxLoop;

    public function __construct(RuntimeRunnerInterface $runner, RuntimeEngineAdapterInterface $engineAdapter, int $maxLoop = 0)
    {
        $this->runner = $runner;
        $this->engineAdapter = $engineAdapter;
        $this->maxLoop = $maxLoop;
    }

    /**
     * @param callable():RunnerRequest $nextRequest
     * @param callable(RunnerResponse):void $emitResponse
     */
    public function run(callable $nextRequest, callable $emitResponse): void
    {
        $this->runner->boot();

        $i = 0;
        while (true) {
            $i++;
            $req = $nextRequest();
            $res = $this->runner->handle($req);

            $emitResponse($res);

            $this->runner->terminate($req, $res);

            $action = $this->engineAdapter->plan(null, $res->getHeader());

            $this->applyAction($action);

            if ($this->maxLoop > 0 && $i >= $this->maxLoop) {
                return;
            }
        }
    }

    private function applyAction(RuntimeEngineAction $action): void
    {
        $type = $action->getType();
        if ($type === 'none') {
            return;
        }
        if ($type === 'gracefulExit') {
            exit(0);
        }
        if ($type === 'hardExit') {
            exit(1);
        }
    }
}
