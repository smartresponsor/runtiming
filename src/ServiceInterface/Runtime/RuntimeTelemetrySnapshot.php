<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeTelemetrySnapshot
{
    public string $namespace;

    /** @var array<string,int> */
    public array $counter = [];

    /** @var array<string,float> */
    public array $gauge = [];

    /** @var array<string,array<string,int|float|string>> */
    public array $meta = [];

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }
}
