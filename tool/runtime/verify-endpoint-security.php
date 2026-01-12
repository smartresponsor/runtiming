<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

$root = dirname(__DIR__, 2);

$expect = [
    'src/Service/Runtime/RuntimeEndpointGuard.php',
    'src/Http/Runtime/RuntimeEndpointGuardSubscriber.php',
    'src/ServiceInterface/Runtime/RuntimeEndpointGuardInterface.php',
    'src/ServiceInterface/Runtime/RuntimeEndpointGuardResult.php',
    'resource/config/package.yaml',
    'resource/config/service-endpoint.yaml',
    'src/Runtime/DependencyInjection/RuntimeSuperchargerConfiguration.php',
    'src/Runtime/DependencyInjection/RuntimeSuperchargerExtension.php',
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
    'resource/config/package.yaml' => ['RUNTIME_ENDPOINT_SECURITY_ENABLED', 'RUNTIME_ENDPOINT_ALLOW_CIDR', 'runtime_supercharger_endpoint_security_mode'],
    'src/Service/Runtime/RuntimeEndpointGuard.php' => ['ipMatchCidr', 'Authorization', 'X-Runtime-Token', 'RuntimeSuperchargerContract::ENDPOINT_STATUS_PATH'],
    'src/Http/Runtime/RuntimeEndpointGuardSubscriber.php' => ['KernelEvents::REQUEST', 'runtime_endpoint_denied'],
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
