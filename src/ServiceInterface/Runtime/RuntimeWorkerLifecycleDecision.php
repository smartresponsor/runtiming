<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

final class RuntimeWorkerLifecycleDecision
{
    public bool $recycle;
    public string $reason;

    private function __construct(bool $recycle, string $reason)
    {
        $this->recycle = $recycle;
        $this->reason = $reason;
    }

    public static function keep(string $reason = 'keep'): self
    {
        return new self(false, $reason);
    }

    public static function recycle(string $reason): self
    {
        return new self(true, $reason);
    }
}
