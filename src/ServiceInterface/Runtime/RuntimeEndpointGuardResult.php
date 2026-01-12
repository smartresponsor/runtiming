<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeEndpointGuardResult
{
    public bool $allowed;
    public string $reason;

    private function __construct(bool $allowed, string $reason)
    {
        $this->allowed = $allowed;
        $this->reason = $reason;
    }

    public static function allow(string $reason = 'allow'): self
    {
        return new self(true, $reason);
    }

    public static function deny(string $reason = 'deny'): self
    {
        return new self(false, $reason);
    }
}
