<?php
declare(strict_types=1);



$root = dirname(__DIR__, 2);

$expect = [
    'README.md',
    'docs/runtime/runtime-supercharger-prod-runbook-01_0.md',
    'docs/runtime/runtime-supercharger-incident-playbook-01_0.md',
    'docs/runtime/runtime-supercharger-debug-checklist-01_0.md',
    'ops/runtime/runtime-supercharger-prod-policy.yaml',
    'tools/runtime/bench/aggregate-status.py',
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
    'docs/runtime/runtime-supercharger-prod-runbook-01_0.md' => ['Go/No-Go', 'Rollback', 'SLO'],
    'tools/runtime/bench/aggregate-status.py' => ['p95', 'failRate'],
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
