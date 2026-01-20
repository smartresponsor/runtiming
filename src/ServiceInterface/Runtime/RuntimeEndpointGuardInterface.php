<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

use Symfony\Component\HttpFoundation\Request;

interface RuntimeEndpointGuardInterface
{
    public function check(Request $request): RuntimeEndpointGuardResult;
}
