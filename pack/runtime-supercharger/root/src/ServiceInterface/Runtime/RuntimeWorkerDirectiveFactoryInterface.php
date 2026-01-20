<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeWorkerDirectiveFactoryInterface
{
    /** @param array<string,string> $header */
    public function fromHeader(array $header): RuntimeWorkerDirective;

    public function fromLifecycle(bool $recycle, string $action, string $reason): RuntimeWorkerDirective;
}
