<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerDirectiveFactoryInterface
{
    /** @param array<string,string> $header */
    public function fromHeader(array $header): RuntimeWorkerDirective;

    public function fromLifecycle(bool $recycle, string $action, string $reason): RuntimeWorkerDirective;
}
