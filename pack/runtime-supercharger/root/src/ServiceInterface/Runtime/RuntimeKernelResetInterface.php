<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeKernelResetInterface
{
    public function reset(): void;
}
