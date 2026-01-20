<?php
declare(strict_types=1);



$root = dirname(__DIR__, 2);

$expect = [
    'README.md',
    'docs/runtime/runtime-supercharger-roadrunner-profile-01_0.md',
    'docs/runtime/runtime-supercharger-status-engine-snippet-01_0.md',
    'resource/template/rr/.rr.yaml',
    'resource/template/rr/env.runtime-rr.env',
    'resource/config/package-engine.yaml',
    'resource/config/service-engine.yaml',
    'src/ServiceInterface/Runtime/RuntimeEngineDetectorInterface.php',
    'src/Service/Runtime/RuntimeEngineDetector.php',
    'tools/rr/run-rr.ps1',
    'tools/rr/run-rr.sh',
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
    'resource/template/rr/.rr.yaml' => ['rpc:', 'http:', 'server:', 'command: "php public/index.php"'],
    'resource/template/rr/env.runtime-rr.env' => ['RUNTIME_ENGINE=rr', 'RUNTIME_WORKER_RESET_ENABLED=1'],
    'src/Service/Runtime/RuntimeEngineDetector.php' => ['getEngineName', 'PHP_SAPI'],
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
