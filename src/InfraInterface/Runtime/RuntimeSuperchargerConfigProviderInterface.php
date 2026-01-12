<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\InfraInterface\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigInterface;

interface RuntimeSuperchargerConfigProviderInterface
{
    public function getConfig(): RuntimeSuperchargerConfigInterface;
}
