<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerSupervisorInterface
{
    public function afterRequest(int $statusCode): RuntimeWorkerDecision;

    public function reset(): void;
}
