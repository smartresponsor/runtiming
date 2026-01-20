<?php
declare(strict_types=1);



use App\Service\Runtime\RuntimeEndpointGuard;
use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

function mk(string $path, string $remote, array $headers = []): Request {
    $server = [
        'REMOTE_ADDR' => $remote,
        'REQUEST_URI' => $path,
    ];

    $r = Request::create($path, 'GET', [], [], [], $server);
    foreach ($headers as $k => $v) {
        $r->headers->set($k, $v);
    }
    return $r;
}

$guard = new RuntimeEndpointGuard(
    '1',
    'allowlist_or_token',
    '127.0.0.1/8,::1/128',
    'tok',
    'X-Runtime-Token',
    '1'
);

// spoofed proxy headers should deny when not from trusted proxy
$r1 = mk('/status', '8.8.8.8', ['X-Forwarded-For' => '127.0.0.1']);
$res1 = $guard->check($r1);
if ($res1->allowed || $res1->reason !== 'proxyHeaderNotTrusted') {
    fwrite(STDERR, "fail: proxy strict did not deny spoofed header\n");
    exit(1);
}

// token still works if no proxy headers
$r2 = mk('/status', '8.8.8.8', ['X-Runtime-Token' => 'tok']);
$res2 = $guard->check($r2);
if (!$res2->allowed) {
    fwrite(STDERR, "fail: token should allow\n");
    exit(1);
}

fwrite(STDOUT, "ok\n");
exit(0);
