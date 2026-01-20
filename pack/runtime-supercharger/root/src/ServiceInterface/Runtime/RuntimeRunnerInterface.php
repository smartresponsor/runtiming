<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeRunnerInterface
{
    public function boot(): void;

    public function handle(RunnerRequest $request): RunnerResponse;

    public function terminate(RunnerRequest $request, RunnerResponse $response): void;
}
