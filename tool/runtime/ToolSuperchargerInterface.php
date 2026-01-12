<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Tool\Runtime;

interface ToolSuperchargerInterface
{
    public function beforeRequest(): void;

    public function afterResponse(int $statusCode): void;
}
