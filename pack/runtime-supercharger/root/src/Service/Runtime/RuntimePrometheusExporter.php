<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimePrometheusExporterInterface;
use App\ServiceInterface\Runtime\RuntimeTelemetrySnapshot;

final class RuntimePrometheusExporter implements RuntimePrometheusExporterInterface
{
    public function export(RuntimeTelemetrySnapshot $snapshot, bool $includeHelp = true): string
    {
        $line = [];

        $type = $this->inferType($snapshot);

        if ($includeHelp) {
            foreach ($type as $name => $t) {
                $line[] = '# HELP ' . $name . ' ' . $this->helpFor($name);
                $line[] = '# TYPE ' . $name . ' ' . $t;
            }
        } else {
            foreach ($type as $name => $t) {
                $line[] = '# TYPE ' . $name . ' ' . $t;
            }
        }

        foreach ($snapshot->counter as $k => $v) {
            [$name, $label] = $this->splitKey($k);
            $line[] = $name . $label . ' ' . (string) $v;
        }

        foreach ($snapshot->gauge as $k => $v) {
            [$name, $label] = $this->splitKey($k);
            $line[] = $name . $label . ' ' . $this->float((float) $v);
        }

        $line[] = '';
        return implode("\n", $line);
    }

    /** @return array<string,string> */
    private function inferType(RuntimeTelemetrySnapshot $snapshot): array
    {
        $out = [];

        foreach ($snapshot->counter as $k => $_) {
            [$name, $_label] = $this->splitKey($k);
            $out[$name] = 'counter';
        }

        foreach ($snapshot->gauge as $k => $_) {
            [$name, $_label] = $this->splitKey($k);
            $out[$name] = $out[$name] ?? 'gauge';
        }

        ksort($out);
        return $out;
    }

    private function helpFor(string $name): string
    {
        return match ($name) {
            'runtime_supercharger_request_total' => 'Total handled requests by engine and status.',
            'runtime_supercharger_request_duration_count' => 'Count of handled requests for duration aggregation.',
            'runtime_supercharger_request_duration_sum' => 'Sum of request durations in seconds.',
            'runtime_supercharger_request_duration_max' => 'Max observed request duration in seconds.',
            'runtime_supercharger_recycle_total' => 'Total recycle requests by engine, action and reason.',
            'runtime_supercharger_memory_high_water_byte' => 'High-water memory usage in bytes.',
            'runtime_supercharger_worker_uptime_second' => 'Worker uptime in seconds.',
            'runtime_supercharger_worker_start_time_second' => 'Worker start time as Unix seconds.',
            default => 'Runtime Supercharger metric.',
        };
    }

    /** @return array{0:string,1:string} */
    private function splitKey(string $k): array
    {
        $pos = strpos($k, '{');
        if ($pos === false) {
            return [$k, ''];
        }

        $name = substr($k, 0, $pos);
        $raw = substr($k, $pos + 1, -1);
        if ($raw === '') {
            return [$name, ''];
        }

        $pairs = explode(',', $raw);
        $buf = [];
        foreach ($pairs as $p) {
            $eq = strpos($p, '=');
            if ($eq === false) {
                continue;
            }
            $lk = substr($p, 0, $eq);
            $lv = substr($p, $eq + 1);
            $buf[] = $this->labelName($lk) . '="' . $this->labelValue($lv) . '"';
        }

        return [$name, '{' . implode(',', $buf) . '}'];
    }

    private function labelName(string $v): string
    {
        $v = preg_replace('/[^a-zA-Z0-9_]/', '_', $v) ?: 'x';
        if (is_numeric($v[0] ?? '')) {
            $v = '_' . $v;
        }
        return $v;
    }

    private function labelValue(string $v): string
    {
        $v = (string) $v;
        $v = str_replace("\\", "\\\\", $v);
        $v = str_replace("\n", "\\n", $v);
        $v = str_replace('"', '\\"', $v);
        return $v;
    }

    private function float(float $v): string
    {
        if (is_nan($v) || is_infinite($v)) {
            return '0';
        }
        return rtrim(rtrim(sprintf('%.6F', $v), '0'), '.');
    }
}
