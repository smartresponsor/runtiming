<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeEngineDetectorInterface
{
    public function getEngineName(): string;
}
