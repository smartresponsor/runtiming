<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimeMetricExporterInterface
{
    public function export(RuntimeMetricRegistryInterface $registry): void;
}
