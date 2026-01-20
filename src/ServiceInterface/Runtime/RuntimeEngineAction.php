<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

final class RuntimeEngineAction
{
    private string $type;
    private string $reason;

    private function __construct(string $type, string $reason)
    {
        $this->type = $type;
        $this->reason = $reason;
    }

    public static function none(): self
    {
        return new self('none', 'none');
    }

    public static function gracefulExit(string $reason): self
    {
        return new self('gracefulExit', $reason);
    }

    public static function hardExit(string $reason): self
    {
        return new self('hardExit', $reason);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function toArray(): array
    {
        return ['type' => $this->type, 'reason' => $this->reason];
    }
}
