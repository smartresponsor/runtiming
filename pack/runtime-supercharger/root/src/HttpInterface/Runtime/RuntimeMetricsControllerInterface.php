<?php
declare(strict_types=1);



namespace App\HttpInterface\Runtime;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

interface RuntimeMetricsControllerInterface
{
    public function metrics(): Response;

    public function aggregate(): JsonResponse;
}
