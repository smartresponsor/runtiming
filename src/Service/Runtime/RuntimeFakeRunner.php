<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeRunnerInterface;
use App\ServiceInterface\Runtime\RunnerRequest;
use App\ServiceInterface\Runtime\RunnerResponse;

final class RuntimeFakeRunner implements RuntimeRunnerInterface
{
    private int $n = 0;

    public function boot(): void
    {
        $this->n = 0;
    }

    public function handle(RunnerRequest $request): RunnerResponse
    {
        $this->n++;

        // simulate: after 3 requests, ask to recycle (like sketch-14/15)
        $header = [];
        if ($this->n >= 3) {
            $header['X-Runtime-Supercharger-Recycle'] = '1';
            $header['X-Runtime-Supercharger-Reason'] = 'maxRequest';
        }

        return new RunnerResponse(200, $header, "ok path=" . $request->getPath() . " n=" . $this->n);
    }

    public function terminate(RunnerRequest $request, RunnerResponse $response): void
    {
        // noop; in real Symfony runner this would call kernel->terminate()
    }
}
