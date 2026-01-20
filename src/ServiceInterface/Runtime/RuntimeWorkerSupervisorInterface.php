<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerSupervisorInterface
{
    public function afterRequest(int $statusCode): RuntimeWorkerDecision;

    public function reset(): void;
}
