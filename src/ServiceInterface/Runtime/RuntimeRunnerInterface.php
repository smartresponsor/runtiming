<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeRunnerInterface
{
    public function boot(): void;

    public function handle(RunnerRequest $request): RunnerResponse;

    public function terminate(RunnerRequest $request, RunnerResponse $response): void;
}
