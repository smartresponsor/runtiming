<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeEngineAdapterInterface
{
    /**
     * @param array<string,string> $header
     */
    public function plan(?object $decision, array $header = []): RuntimeEngineAction;
}
