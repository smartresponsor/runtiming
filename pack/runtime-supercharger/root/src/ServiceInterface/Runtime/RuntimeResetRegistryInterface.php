<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeResetRegistryInterface
{
    public function resetAll(): RuntimeResetReport;
}
