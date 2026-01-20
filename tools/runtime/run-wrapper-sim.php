<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Service\Runtime\RuntimeEngineAdapter;
use App\ServiceInterface\Runtime\RunnerRequest;
use App\ServiceInterface\Runtime\RunnerResponse;
use App\ServiceInterface\Runtime\RuntimeRunnerInterface;

final class DemoRunner implements RuntimeRunnerInterface
{
    public function boot(): void
    {
    }

    public function handle(RunnerRequest $request): RunnerResponse
    {
        $path = $request->getPath();

        $header = [
            'X-Runtime-Supercharger-Recycle' => '0',
        ];

        if ($path === '/b') {
            $header['X-Runtime-Supercharger-Recycle'] = '1';
            $header['X-Runtime-Supercharger-Reason'] = 'memory_limit';
        }

        return new RunnerResponse(200, $header, json_encode(['path' => $path], JSON_UNESCAPED_SLASHES));
    }

    public function terminate(RunnerRequest $request, RunnerResponse $response): void
    {
    }
}

$adapter = new RuntimeEngineAdapter(false);
$runner = new DemoRunner();
$runner->boot();

$paths = ['/a', '/b', '/c'];
$out = [];

foreach ($paths as $p) {
    $req = new RunnerRequest('GET', $p);
    $res = $runner->handle($req);
    $runner->terminate($req, $res);

    $action = $adapter->plan(null, $res->getHeader());

    $out[] = [
        'path' => $p,
        'header' => $res->getHeader(),
        'action' => $action->toArray(),
    ];
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
