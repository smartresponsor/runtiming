<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeResetInterface
{
    public function getRuntimeResetName(): string;

    public function reset(): void;
}
