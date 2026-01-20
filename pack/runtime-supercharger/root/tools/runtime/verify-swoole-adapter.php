<?php
declare(strict_types=1);



$root = dirname(__DIR__, 2);

$expect = [
    'README.md',
    'docs/runtime/runtime-supercharger-swoole-profile-01_0.md',
    'resource/template/swoole/env.runtime-swoole.env',
    'resource/template/swoole/docker-compose.swoole.yaml',
    'docs/runtime/runtime-supercharger-swoole-checklist-01_0.md',
    'tools/swoole/run-swoole.ps1',
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
    'resource/template/swoole/env.runtime-swoole.env' => ['RUNTIME_ENGINE=swoole', 'RUNTIME_WORKER_RESET_ENABLED=1'],
    'resource/template/swoole/docker-compose.swoole.yaml' => ['pecl install swoole', 'php:8.5-cli'],
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
