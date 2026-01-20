<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

interface RuntimeMetricRegistryInterface
{
    /**
     * @param array<string,string> $label
     */
    public function incCounter(string $name, array $label = [], int $value = 1): void;

    /**
     * @param array<string,string> $label
     */
    public function setGauge(string $name, float $value, array $label = []): void;

    /**
     * Summary: keep sum/count only.
     *
     * @param array<string,string> $label
     */
    public function observeSummary(string $name, float $value, array $label = []): void;

    public function renderText(): string;
}
