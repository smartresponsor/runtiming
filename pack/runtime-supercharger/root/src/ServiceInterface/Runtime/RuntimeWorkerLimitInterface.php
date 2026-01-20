<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerLimitInterface
{
    public function getMaxRequest(): int;

    public function getMaxUptimeSec(): int;

    public function getMaxMemoryMb(): int;

    public function getSoftMemoryMb(): int;
}
