<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

final class RuntimeLifecycleDecision
{
    public bool $recycle;
    public string $action;
    public string $reason;

    /** @var array<string,string> */
    public array $meta;

    /** @param array<string,string> $meta */
    public function __construct(bool $recycle, string $action, string $reason, array $meta = [])
    {
        $this->recycle = $recycle;
        $this->action = $action;
        $this->reason = $reason;
        $this->meta = $meta;
    }

    public static function none(): self
    {
        return new self(false, 'none', 'none', []);
    }
}
