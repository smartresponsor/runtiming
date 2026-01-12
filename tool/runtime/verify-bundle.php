<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

$root = dirname(__DIR__, 2);
$expect = [
    'src/Runtime/RuntimeSuperchargerBundle.php',
    'src/Runtime/RuntimeSuperchargerContract.php',
    'src/Runtime/DependencyInjection/RuntimeSuperchargerExtension.php',
    'src/Runtime/DependencyInjection/RuntimeSuperchargerConfiguration.php',
    'resource/config/package.yaml',
    'resource/config/service-core.yaml',
    'resource/config/service-endpoint.yaml',
    'resource/config/route-metrics.yaml',
    'resource/config/route-status.yaml',
];

$fail = 0;

foreach ($expect as $rel) {
    $p = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (!is_file($p)) {
        fwrite(STDERR, "missing: $rel\n");
        $fail++;
        continue;
    }
    $s = file_get_contents($p);
    if (!is_string($s) || trim($s) === '') {
        fwrite(STDERR, "empty: $rel\n");
        $fail++;
    }
}

$checks = [
    'resource/config/route-metrics.yaml' => ['/metrics', '/runtime/metrics/aggregate', 'RuntimeMetricsController'],
    'resource/config/route-status.yaml' => ['/status', '/runtime/status/host', 'RuntimeStatusController'],
    'resource/config/service-core.yaml' => ['runtime_supercharger_telemetry_dir', 'RuntimeTelemetryAggregate', 'RuntimePrometheusExporter'],
];

foreach ($checks as $rel => $need) {
    $p = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $s = is_file($p) ? file_get_contents($p) : '';
    $s = is_string($s) ? $s : '';
    foreach ($need as $n) {
        if (strpos($s, $n) === false) {
            fwrite(STDERR, "check failed: $rel lacks '$n'\n");
            $fail++;
        }
    }
}

if ($fail > 0) {
    fwrite(STDERR, "fail=$fail\n");
    exit(1);
}

fwrite(STDOUT, "ok\n");
exit(0);
