<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

$root = dirname(__DIR__, 2);

$expect = [
    'README.md',
    'docs/runtime/runtime-supercharger-frankenphp-profile-01_0.md',
    'tools/frankenphp/template/Caddyfile',
    'tools/frankenphp/template/env.runtime-frankenphp.env',
    'tools/frankenphp/run-frankenphp.ps1',
    'tools/frankenphp/run-frankenphp.sh',
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
    'tools/frankenphp/template/Caddyfile' => ['php_server', 'root * public', ':8080'],
    'tools/frankenphp/template/env.runtime-frankenphp.env' => ['RUNTIME_ENGINE=frankenphp', 'RUNTIME_WORKER_RESET_ENABLED=1'],
    'tools/frankenphp/run-frankenphp.ps1' => ['Find-Frankenphp', 'Caddyfile', '--config'],
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
