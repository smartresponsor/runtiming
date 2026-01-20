<?php
declare(strict_types=1);



$root = dirname(__DIR__, 2);

$expect = [
    'README.md',
    'docs/runtime/runtime-supercharger-worker-lifecycle-01_0.md',
    'src/Runtime/DependencyInjection/RuntimeSuperchargerConfiguration.php',
    'src/Runtime/DependencyInjection/RuntimeSuperchargerExtension.php',
    'resource/config/package.yaml',
    'resource/config/service-worker.yaml',
    'src/ServiceInterface/Runtime/RuntimeWorkerLifecyclePolicyInterface.php',
    'src/ServiceInterface/Runtime/RuntimeWorkerLifecycleDecision.php',
    'src/ServiceInterface/Runtime/RuntimeWorkerTerminatorInterface.php',
    'src/ServiceInterface/Runtime/RuntimeWorkerStateInterface.php',
    'src/Service/Runtime/RuntimeWorkerLifecyclePolicy.php',
    'src/Service/Runtime/RuntimeWorkerTerminator.php',
    'src/Service/Runtime/RuntimeWorkerState.php',
    'src/Http/Runtime/RuntimeWorkerLifecycleSubscriber.php',
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
    'resource/config/package.yaml' => ['RUNTIME_WORKER_MAX_REQUEST', 'runtime_supercharger_worker_max_memory_mb'],
    'src/Http/Runtime/RuntimeWorkerLifecycleSubscriber.php' => ['KernelEvents::TERMINATE', 'X-Runtime-Recycle', 'Connection'],
    'src/Service/Runtime/RuntimeWorkerState.php' => ['memory_get_usage', 'pcntl_signal', 'markRecycle'],
    'src/Service/Runtime/RuntimeWorkerTerminator.php' => ['exit(0)'],
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
