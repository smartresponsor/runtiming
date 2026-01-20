<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeSuperchargerInterface
{
    public function beforeRequest(): void;

    public function afterResponse(int $statusCode): void;
}
