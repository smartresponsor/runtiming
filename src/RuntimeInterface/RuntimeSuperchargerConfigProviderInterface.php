<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\RuntimeInterface;

use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigInterface;

interface RuntimeSuperchargerConfigProviderInterface
{
    public function getConfig(): RuntimeSuperchargerConfigInterface;
}
