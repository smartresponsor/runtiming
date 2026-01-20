<?php
declare(strict_types=1);



$root = dirname(__DIR__, 2);

$expect = [
    'docs/runtime/runtime-supercharger-trusted-proxy-01_0.md',
    'resource/template/framework-trusted-proxy.yaml',
    'resource/template/dotenv-trusted-proxy.env',
    'src/Service/Runtime/RuntimeEndpointGuard.php',
    'src/Runtime/DependencyInjection/RuntimeSuperchargerConfiguration.php',
    'src/Runtime/DependencyInjection/RuntimeSuperchargerExtension.php',
    'resource/config/package.yaml',
    'resource/config/service-endpoint.yaml',
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
    'src/Service/Runtime/RuntimeEndpointGuard.php' => ['proxyHeaderNotTrusted', 'hasProxyHeader', 'getTrustedProxies', 'RUNTIME_ENDPOINT_PROXY_STRICT'],
    'resource/config/package.yaml' => ['RUNTIME_ENDPOINT_PROXY_STRICT', 'runtime_supercharger_endpoint_proxy_strict'],
    'src/Runtime/DependencyInjection/RuntimeSuperchargerConfiguration.php' => ['proxy_strict'],
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
