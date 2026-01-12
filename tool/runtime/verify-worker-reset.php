<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

$root = dirname(__DIR__, 2);

$expect = [
    'docs/runtime/runtime-supercharger-worker-reset-01_0.md',
    'src/ServiceInterface/Runtime/RuntimeResetRegistryInterface.php',
    'src/ServiceInterface/Runtime/RuntimeResetterInterface.php',
    'src/ServiceInterface/Runtime/RuntimeResetReport.php',
    'src/Service/Runtime/RuntimeResetRegistry.php',
    'src/Service/Runtime/RuntimeKernelResetter.php',
    'src/Service/Runtime/RuntimeDoctrineResetter.php',
    'src/Http/Runtime/RuntimeWorkerResetSubscriber.php',
    'resource/config/service-worker-reset.yaml',
    'resource/config/package.yaml',
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
    'resource/config/package.yaml' => ['RUNTIME_WORKER_RESET_ENABLED', 'RUNTIME_WORKER_RESET_KERNEL', 'RUNTIME_WORKER_RESET_DOCTRINE'],
    'resource/config/service-worker-reset.yaml' => ['runtime_supercharger_resetter', 'services_resetter', '@?doctrine'],
    'src/Http/Runtime/RuntimeWorkerResetSubscriber.php' => ['KernelEvents::TERMINATE', 'resetAll'],
    'src/Service/Runtime/RuntimeDoctrineResetter.php' => ['resetManager', 'getManagers', 'clear'],
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
