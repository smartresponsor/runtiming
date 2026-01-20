<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeSuperchargerInterface
{
    public function beforeRequest(): void;

    public function afterResponse(int $statusCode): void;
}
