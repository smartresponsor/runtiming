<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerTerminatorInterface
{
    public function terminate(string $reason): void;
}
