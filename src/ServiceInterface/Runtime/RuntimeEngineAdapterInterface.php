<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeEngineAdapterInterface
{
    /**
     * @param array<string,string> $header
     */
    public function plan(?object $decision, array $header = []): RuntimeEngineAction;
}
