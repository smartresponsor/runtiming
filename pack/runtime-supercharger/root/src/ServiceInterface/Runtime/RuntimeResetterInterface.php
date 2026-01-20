<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeResetterInterface
{
    public function reset(RuntimeResetReport $report): void;
}
