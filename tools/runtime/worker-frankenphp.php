<?php
declare(strict_types=1);



require_once __DIR__ . '/../../vendor/autoload.php';

use App\Service\Runtime\RuntimeWorkerDirectiveFactory;
use App\ServiceInterface\Runtime\RunnerResponse;

if (!function_exists('frankenphp_handle_request')) {
    fwrite(STDERR, "This script must run under FrankenPHP worker mode.\n");
    exit(2);
}

ignore_user_abort(true);

$entry = (string) getenv('RUNTIME_APP_ENTRY');
if ($entry === '') {
    fwrite(STDERR, "RUNTIME_APP_ENTRY is required. It must return callable(array $context): RunnerResponse\n");
    exit(2);
}

$entryPath = $entry;
if (!is_file($entryPath)) {
    $entryPath = __DIR__ . '/../../' . ltrim($entry, '/');
}
if (!is_file($entryPath)) {
    fwrite(STDERR, "RUNTIME_APP_ENTRY not found: {$entry}\n");
    exit(2);
}

$app = require $entryPath;
if (!is_callable($app)) {
    fwrite(STDERR, "RUNTIME_APP_ENTRY must return a callable.\n");
    exit(2);
}

$directiveFactory = new RuntimeWorkerDirectiveFactory();
$exitCode = 0;
$fixtureCount = 0;

$handler = static function () use (&$exitCode, &$fixtureCount, $app, $directiveFactory): void {
    $fixtureCount++;

    $context = [
        'engine' => 'frankenphp',
        'nowFloat' => microtime(true),
        'server' => $_SERVER,
        'get' => $_GET,
        'post' => $_POST,
        'cookie' => $_COOKIE,
        'files' => $_FILES,
        'input' => (string) file_get_contents('php://input'),
        'fixtureCount' => $fixtureCount,
        'fixtureMax' => (int) ($_SERVER['RUNTIME_FIXTURE_MAX'] ?? 0),
    ];

    /** @var RunnerResponse $res */
    $res = $app($context);

    http_response_code($res->getStatus());
    foreach ($res->getHeader() as $k => $v) {
        header($k . ': ' . $v, true);
    }

    echo $res->getBody();

    $directive = $directiveFactory->fromHeader($res->getHeader());
    if ($directive->stop) {
        $exitCode = $directive->exitCode;
    }
};

$maxRequests = (int) ($_SERVER['MAX_REQUESTS'] ?? 0);
for ($i = 0; !$maxRequests || $i < $maxRequests; $i++) {
    $keepRunning = frankenphp_handle_request($handler);

    gc_collect_cycles();

    if (!$keepRunning) {
        break;
    }

    if ($exitCode !== 0) {
        break;
    }
}

// Exit code 0 still triggers a normal stop; non-zero indicates a crash-style recycle.
exit($exitCode);
