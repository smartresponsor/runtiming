<?php
declare(strict_types=1);



namespace App\Tool\Runtime\Fixture;

interface FixtureWorkerSupervisorInterface
{
    public function afterRequest(int $statusCode): FixtureWorkerDecision;
}
