<?php
declare(strict_types=1);



$root = dirname(__DIR__, 2);

$expect = [
    'README.md',
    'docs/runtime/runtime-supercharger-bench-soak-01_0.md',
    'tools/runtime/bench/run-bench.ps1',
    'tools/runtime/bench/sample-status.ps1',
    'tools/runtime/bench/run-bench.sh',
    'tools/runtime/bench/sample-status.sh',
    'tools/runtime/bench/parse-prom-metrics.py',
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

$needle = [
    'tools/runtime/bench/run-bench.ps1' => ['RUNTIME_BENCH_BASE_URL', 'summary.json', 'metrics.json'],
    'tools/runtime/bench/parse-prom-metrics.py' => ['Prometheus', 'line_re', 'labels'],
];

foreach ($needle as $rel => $list) {
    $p = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $s = is_file($p) ? file_get_contents($p) : '';
    $s = is_string($s) ? $s : '';
    foreach ($list as $n) {
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
