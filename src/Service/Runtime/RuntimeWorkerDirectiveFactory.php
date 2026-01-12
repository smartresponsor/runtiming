<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerDirective;
use App\ServiceInterface\Runtime\RuntimeWorkerDirectiveFactoryInterface;
use App\ServiceInterface\Runtime\RuntimeWorkerRecycleHeader;

final class RuntimeWorkerDirectiveFactory implements RuntimeWorkerDirectiveFactoryInterface
{
    /** @param array<string,string> $header */
    public function fromHeader(array $header): RuntimeWorkerDirective
    {
        $recycle = isset($header[RuntimeWorkerRecycleHeader::RECYCLE]) && $header[RuntimeWorkerRecycleHeader::RECYCLE] === '1';
        $action = (string) ($header[RuntimeWorkerRecycleHeader::ACTION] ?? 'none');
        $reason = (string) ($header[RuntimeWorkerRecycleHeader::REASON] ?? 'none');

        return $this->fromLifecycle($recycle, $action, $reason);
    }

    public function fromLifecycle(bool $recycle, string $action, string $reason): RuntimeWorkerDirective
    {
        if (!$recycle) {
            return RuntimeWorkerDirective::none();
        }

        $exitCode = 0;
        if ($action === RuntimeWorkerRecycleHeader::ACTION_HARD) {
            $exitCode = 1;
        }

        return new RuntimeWorkerDirective(true, $exitCode, $action, $reason);
    }
}
