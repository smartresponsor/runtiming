<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerStatInterface
{
    public function getUptimeSec(): int;

    public function getRssMemoryMb(): int;

    public function incRequest(): int;

    public function reset(): void;
}
