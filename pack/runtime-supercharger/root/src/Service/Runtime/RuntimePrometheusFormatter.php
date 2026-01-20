<?php
declare(strict_types=1);



namespace App\Service\Runtime;

final class RuntimePrometheusFormatter
{
    /**
     * @param array<string,string> $label
     */
    public function formatKey(string $name, array $label): string
    {
        $safeName = $this->safeName($name);
        if (count($label) === 0) {
            return $safeName;
        }

        ksort($label);
        $part = [];
        foreach ($label as $k => $v) {
            $k2 = $this->safeLabelName($k);
            $v2 = $this->escapeLabelValue($v);
            $part[] = $k2 . '="' . $v2 . '"';
        }

        return $safeName . '{' . implode(',', $part) . '}';
    }

    public function safeName(string $name): string
    {
        $n = preg_replace('/[^a-zA-Z0-9_:]/', '_', $name);
        if (!is_string($n) || $n === '') {
            return '_';
        }
        if (!preg_match('/^[a-zA-Z_:]/', $n)) {
            return '_' . $n;
        }
        return $n;
    }

    public function safeLabelName(string $name): string
    {
        $n = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        if (!is_string($n) || $n === '') {
            return '_';
        }
        if (!preg_match('/^[a-zA-Z_]/', $n)) {
            return '_' . $n;
        }
        return $n;
    }

    private function escapeLabelValue(string $v): string
    {
        $v = str_replace(['\\', "\n", '"'], ['\\\\', '\\n', '\\"'], $v);
        return $v;
    }
}
