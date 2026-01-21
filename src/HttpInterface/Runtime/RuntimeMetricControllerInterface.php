<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\HttpInterface\Runtime;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

interface RuntimeMetricControllerInterface
{
    public function metric(): Response;

    public function aggregate(): JsonResponse;
}
