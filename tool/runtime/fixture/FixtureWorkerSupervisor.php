<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Tool\Runtime\Fixture;

final class FixtureWorkerSupervisor implements FixtureWorkerSupervisorInterface
{
    private int $count = 0;

    public function afterRequest(int $statusCode): FixtureWorkerDecision
    {
        $this->count++;
        $shouldRecycle = $this->count >= 3;

        return new FixtureWorkerDecision([
            'shouldRecycle' => $shouldRecycle,
            'reason' => $shouldRecycle ? 'maxRequest' : 'ok',
            'requestCount' => $this->count,
            'uptimeSec' => 0,
            'rssMemoryMb' => 0,
            'statusCode' => $statusCode,
        ]);
    }
}
