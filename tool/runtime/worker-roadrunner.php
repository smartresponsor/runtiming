<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RunnerResponse.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerRecycleHeader.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerDirective.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeWorkerDirectiveFactoryInterface.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeWorkerDirectiveFactory.php';

use App\Service\Runtime\RuntimeWorkerDirectiveFactory;
use App\ServiceInterface\Runtime\RunnerResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker;

if (!class_exists(Worker::class) || !class_exists(PSR7Worker::class)) {
    fwrite(STDERR, "Missing RoadRunner packages. Install: composer require spiral/roadrunner-http nyholm/psr7\n");
    exit(2);
}

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

$worker = Worker::create();
$factory = new Psr17Factory();
$psr7 = new PSR7Worker($worker, $factory, $factory, $factory);

$exitCode = 0;
$fixtureCount = 0;

while (true) {
    try {
        $request = $psr7->waitRequest();
        if ($request === null) {
            break;
        }
    } catch (\Throwable $e) {
        $psr7->respond(new Response(400));
        continue;
    }

    try {
        $fixtureCount++;

        $context = [
            'engine' => 'roadrunner',
            'nowFloat' => microtime(true),
            'psr7Request' => $request,
            'fixtureCount' => $fixtureCount,
            'fixtureMax' => (int) (getenv('RUNTIME_FIXTURE_MAX') ?: 0),
        ];

        /** @var RunnerResponse $res */
        $res = $app($context);

        $psr = new Response($res->getStatus(), $res->getHeader(), $res->getBody());
        $psr7->respond($psr);

        $directive = $directiveFactory->fromHeader($res->getHeader());
        if ($directive->stop) {
            $exitCode = $directive->exitCode;
            break;
        }
    } catch (\Throwable $e) {
        $psr7->respond(new Response(500, [], 'Internal Error'));
        // Optionally report error to RR:
        // $psr7->getWorker()->error((string) $e);
        continue;
    }
}

exit($exitCode);
