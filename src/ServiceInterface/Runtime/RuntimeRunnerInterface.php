<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeRunnerInterface
{
    public function boot(): void;

    public function handle(RunnerRequest $request): RunnerResponse;

    public function terminate(RunnerRequest $request, RunnerResponse $response): void;
}
