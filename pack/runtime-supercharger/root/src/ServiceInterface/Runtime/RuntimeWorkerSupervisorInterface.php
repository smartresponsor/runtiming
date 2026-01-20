<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerSupervisorInterface
{
    public function afterRequest(int $statusCode): RuntimeWorkerDecision;

    public function reset(): void;
}
