<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\HttpInterface\Runtime;

use Symfony\Component\HttpFoundation\JsonResponse;

interface RuntimeStatusControllerInterface
{
    public function worker(): JsonResponse;

    public function host(): JsonResponse;
}
