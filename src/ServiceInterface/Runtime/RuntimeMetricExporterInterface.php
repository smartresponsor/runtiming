<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeMetricExporterInterface
{
    public function export(RuntimeMetricRegistryInterface $registry): void;
}
