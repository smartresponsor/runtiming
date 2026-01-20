<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeMetricRegistryInterface;

final class RuntimePrometheusRegistry implements RuntimeMetricRegistryInterface
{
    /** @var array<string,int> */
    private array $counter = [];

    /** @var array<string,float> */
    private array $gauge = [];

    /** @var array<string,float> */
    private array $summarySum = [];

    /** @var array<string,int> */
    private array $summaryCount = [];

    private RuntimePrometheusFormatter $fmt;

    public function __construct(?RuntimePrometheusFormatter $fmt = null)
    {
        $this->fmt = $fmt ?? new RuntimePrometheusFormatter();
    }

    /** @param array<string,string> $label */
    public function incCounter(string $name, array $label = [], int $value = 1): void
    {
        $key = $this->fmt->formatKey($name, $label);
        $this->counter[$key] = ($this->counter[$key] ?? 0) + max(0, $value);
    }

    /** @param array<string,string> $label */
    public function setGauge(string $name, float $value, array $label = []): void
    {
        $key = $this->fmt->formatKey($name, $label);
        $this->gauge[$key] = $value;
    }

    /** @param array<string,string> $label */
    public function observeSummary(string $name, float $value, array $label = []): void
    {
        $sumKey = $this->fmt->formatKey($name . '_sum', $label);
        $cntKey = $this->fmt->formatKey($name . '_count', $label);

        $this->summarySum[$sumKey] = ($this->summarySum[$sumKey] ?? 0.0) + $value;
        $this->summaryCount[$cntKey] = ($this->summaryCount[$cntKey] ?? 0) + 1;
    }

    public function renderText(): string
    {
        $line = [];

        ksort($this->counter);
        foreach ($this->counter as $k => $v) {
            $line[] = $k . ' ' . (string) $v;
        }

        ksort($this->gauge);
        foreach ($this->gauge as $k => $v) {
            $line[] = $k . ' ' . $this->formatFloat($v);
        }

        ksort($this->summarySum);
        foreach ($this->summarySum as $k => $v) {
            $line[] = $k . ' ' . $this->formatFloat($v);
        }

        ksort($this->summaryCount);
        foreach ($this->summaryCount as $k => $v) {
            $line[] = $k . ' ' . (string) $v;
        }

        return implode("\n", $line) . "\n";
    }

    private function formatFloat(float $v): string
    {
        if (is_nan($v)) {
            return 'NaN';
        }
        if ($v === INF) {
            return '+Inf';
        }
        if ($v === -INF) {
            return '-Inf';
        }
        // Stable: no scientific notation for common values.
        $s = rtrim(rtrim(sprintf('%.6F', $v), '0'), '.');
        return $s === '' ? '0' : $s;
    }
}
