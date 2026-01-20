<?php
declare(strict_types=1);



namespace App\Service\Runtime;

final class RuntimeSymfonyResetReport
{
    public function __construct(
        public readonly bool $kernelResetAttempted,
        public readonly float $kernelResetMs,
        public readonly RuntimeResetReport $domainReset,
        public readonly bool $gcCollectAttempted,
        public readonly float $gcCollectMs,
        public readonly float $totalMs,
    ) {
    }

    public function toArray(): array
    {
        return [
            'kernel_reset_attempted' => $this->kernelResetAttempted,
            'kernel_reset_ms' => $this->kernelResetMs,
            'domain_reset' => $this->domainReset->toArray(),
            'gc_collect_attempted' => $this->gcCollectAttempted,
            'gc_collect_ms' => $this->gcCollectMs,
            'total_ms' => $this->totalMs,
        ];
    }
}
