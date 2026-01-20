<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

final class RuntimeWorkerDirective
{
    public bool $stop;
    public int $exitCode;
    public string $action;
    public string $reason;

    public function __construct(bool $stop, int $exitCode, string $action, string $reason)
    {
        $this->stop = $stop;
        $this->exitCode = $exitCode;
        $this->action = $action;
        $this->reason = $reason;
    }

    public static function none(): self
    {
        return new self(false, 0, 'none', 'none');
    }
}
