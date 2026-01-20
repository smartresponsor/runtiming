<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

interface RuntimePrometheusExporterInterface
{
    public function export(RuntimeTelemetrySnapshot $snapshot, bool $includeHelp = true): string;
}
