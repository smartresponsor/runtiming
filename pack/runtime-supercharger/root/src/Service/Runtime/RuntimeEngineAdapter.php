<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeEngineAction;
use App\ServiceInterface\Runtime\RuntimeEngineAdapterInterface;

final class RuntimeEngineAdapter implements RuntimeEngineAdapterInterface
{
    private bool $hardExitOnMemory;

    public function __construct(bool $hardExitOnMemory = false)
    {
        $this->hardExitOnMemory = $hardExitOnMemory;
    }

    /** @param array<string,string> $header */
    public function plan(?object $decision, array $header = []): RuntimeEngineAction
    {
        $shouldRecycle = false;
        $reason = 'unknown';

        if (is_object($decision)) {
            $shouldRecycle = $this->readDecisionRecycle($decision);
            $reason = $this->readDecisionReason($decision) ?? $reason;
        } else {
            $shouldRecycle = $this->readHeaderRecycle($header);
            $reason = $this->readHeaderReason($header) ?? $reason;
        }

        if (!$shouldRecycle) {
            return RuntimeEngineAction::none();
        }

        if ($this->hardExitOnMemory && $this->isMemoryReason($reason)) {
            return RuntimeEngineAction::hardExit($reason);
        }

        return RuntimeEngineAction::gracefulExit($reason);
    }

    private function readDecisionRecycle(object $decision): bool
    {
        if (method_exists($decision, 'getShouldRecycle')) {
            return (bool) $decision->getShouldRecycle();
        }
        if (method_exists($decision, 'toArray')) {
            $a = $decision->toArray();
            if (is_array($a) && array_key_exists('shouldRecycle', $a)) {
                return (bool) $a['shouldRecycle'];
            }
        }
        return false;
    }

    private function readDecisionReason(object $decision): ?string
    {
        if (method_exists($decision, 'getReason')) {
            $r = $decision->getReason();
            return is_string($r) ? $r : (string) $r;
        }
        if (method_exists($decision, 'toArray')) {
            $a = $decision->toArray();
            if (is_array($a) && isset($a['reason'])) {
                return is_string($a['reason']) ? $a['reason'] : (string) $a['reason'];
            }
        }
        return null;
    }

    /** @param array<string,string> $header */
    private function readHeaderRecycle(array $header): bool
    {
        $v = $this->headerValue($header, 'X-Runtime-Supercharger-Recycle');
        return $v === '1' || strtolower($v) === 'true';
    }

    /** @param array<string,string> $header */
    private function readHeaderReason(array $header): ?string
    {
        $v = $this->headerValue($header, 'X-Runtime-Supercharger-Reason');
        return $v === '' ? null : $v;
    }

    /** @param array<string,string> $header */
    private function headerValue(array $header, string $name): string
    {
        foreach ($header as $k => $v) {
            if (strcasecmp($k, $name) === 0) {
                return (string) $v;
            }
        }
        return '';
    }

    private function isMemoryReason(string $reason): bool
    {
        $r = strtolower($reason);
        return strpos($r, 'memory') !== false || strpos($r, 'maxmemory') !== false || strpos($r, 'oom') !== false;
    }
}
