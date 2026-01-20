<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeResetInterface
{
    public function getRuntimeResetName(): string;

    public function reset(): void;
}
