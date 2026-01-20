<?php
declare(strict_types=1);



namespace App\Tool\Runtime;

interface ToolSuperchargerInterface
{
    public function beforeRequest(): void;

    public function afterResponse(int $statusCode): void;
}
