<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

use App\ServiceInterface\Runtime\RunnerResponse;
use App\ServiceInterface\Runtime\RuntimeWorkerRecycleHeader;

return static function (array $context): RunnerResponse {
    $engine = (string) ($context['engine'] ?? 'unknown');

    $res = new RunnerResponse(200, ['Content-Type' => 'text/plain'], "ok engine={$engine}\n");

    // Fixture: request recycle after a few calls if provided by context.
    $n = (int) ($context['fixtureCount'] ?? 0);
    $max = (int) ($context['fixtureMax'] ?? 0);
    if ($max > 0 && $n >= $max) {
        $res = $res
            ->withHeader(RuntimeWorkerRecycleHeader::RECYCLE, '1')
            ->withHeader(RuntimeWorkerRecycleHeader::ACTION, RuntimeWorkerRecycleHeader::ACTION_GRACEFUL)
            ->withHeader(RuntimeWorkerRecycleHeader::REASON, 'fixtureMax');
    }

    return $res;
};
