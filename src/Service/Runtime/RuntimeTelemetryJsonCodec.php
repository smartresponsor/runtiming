<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeTelemetrySnapshot;

final class RuntimeTelemetryJsonCodec
{
    public function decode(string $json): ?RuntimeTelemetrySnapshot
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }

        $ns = (string) ($data['namespace'] ?? 'runtime');
        $snap = new RuntimeTelemetrySnapshot($ns);

        $counter = $data['counter'] ?? [];
        $gauge = $data['gauge'] ?? [];
        $meta = $data['meta'] ?? [];

        if (is_array($counter)) {
            foreach ($counter as $k => $v) {
                if (is_string($k) && (is_int($v) || (is_numeric($v) && (int) $v >= 0))) {
                    $snap->counter[$k] = (int) $v;
                }
            }
        }

        if (is_array($gauge)) {
            foreach ($gauge as $k => $v) {
                if (is_string($k) && (is_float($v) || is_int($v) || is_numeric($v))) {
                    $snap->gauge[$k] = (float) $v;
                }
            }
        }

        if (is_array($meta)) {
            $snap->meta = $meta;
        }

        return $snap;
    }
}
