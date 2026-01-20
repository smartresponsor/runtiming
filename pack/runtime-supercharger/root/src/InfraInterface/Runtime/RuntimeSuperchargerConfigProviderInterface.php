<?php
declare(strict_types=1);



namespace App\InfraInterface\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigInterface;

interface RuntimeSuperchargerConfigProviderInterface
{
    public function getConfig(): RuntimeSuperchargerConfigInterface;
}
