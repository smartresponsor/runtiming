<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

use Symfony\Component\HttpFoundation\Request;

interface RuntimeEndpointGuardInterface
{
    public function check(Request $request): RuntimeEndpointGuardResult;
}
