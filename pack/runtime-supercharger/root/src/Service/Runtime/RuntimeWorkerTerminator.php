<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeWorkerTerminatorInterface;

final class RuntimeWorkerTerminator implements RuntimeWorkerTerminatorInterface
{
    private string $enabledRaw;

    public function __construct(string $enabled)
    {
        $this->enabledRaw = $enabled;
    }

    public function terminate(string $reason): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Best-effort: if response is already sent, exit ends the worker and supervisor respawns it.
        // This is the simplest portable behavior across long-running engines.
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }

        // Avoid throwing exceptions from terminate path.
        exit(0);
    }

    private function isEnabled(): bool
    {
        $v = strtolower(trim($this->enabledRaw));
        if ($v === '' || $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on') {
            return true;
        }
        if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
            return false;
        }
        return true;
    }
}
