<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeResetReport
{
    public int $resetCount;
    public int $errorCount;
    public int $durationMs;

    /**
     * @var list<string>
     */
    public array $error;

    public function __construct()
    {
        $this->resetCount = 0;
        $this->errorCount = 0;
        $this->durationMs = 0;
        $this->error = [];
    }

    public function addError(string $message): void
    {
        $this->errorCount++;
        $this->error[] = $message;
    }
}
