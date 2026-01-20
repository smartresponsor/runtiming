<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeMetricExporterInterface;
use App\ServiceInterface\Runtime\RuntimeMetricRegistryInterface;

final class RuntimeNullMetricExporter implements RuntimeMetricExporterInterface
{
    public function export(RuntimeMetricRegistryInterface $registry): void
    {
        // no-op
    }
}
