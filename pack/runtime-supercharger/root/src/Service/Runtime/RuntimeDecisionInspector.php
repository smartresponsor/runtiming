<?php
declare(strict_types=1);



namespace App\Service\Runtime;

final class RuntimeDecisionInspector
{
    public function shouldRecycle(object $decision): bool
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

    public function reason(object $decision): string
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

        return 'unknown';
    }
}
