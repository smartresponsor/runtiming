<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeNdjsonSinkInterface;

final class RuntimeNullNdjsonSink implements RuntimeNdjsonSinkInterface
{
    /** @param array<string,mixed> $event */
    public function emit(array $event): void
    {
        // no-op
    }
}
