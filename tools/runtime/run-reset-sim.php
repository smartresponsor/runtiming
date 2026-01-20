<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

require_once __DIR__ . '/fixture/FixtureCounterResetter.php';

use App\Service\Runtime\RuntimeResetMiddleware;
use App\Service\Runtime\RuntimeResetRegistry;
use App\Tool\Runtime\Fixture\FixtureCounterResetter;

$registry = new RuntimeResetRegistry(true);
$counter = new FixtureCounterResetter();
$registry->add($counter);

$mw = new RuntimeResetMiddleware($registry, true, true);

$served = 0;

for ($i = 0; $i < 3; $i++) {
    $mw->call(static function () use (&$served): void {
        $served++;
    });
}

$out = [
    'served' => $served,
    'resetCount' => $counter->getCount(),
    'resetName' => $registry->listName(),
];

fwrite(STDOUT, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
