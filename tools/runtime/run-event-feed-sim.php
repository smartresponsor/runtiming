<?php
declare(strict_types=1);



require_once dirname(__DIR__, 2) . '/vendor/autoload.php';



use App\Service\Runtime\RuntimeNdjsonFileSink;
use App\ServiceInterface\Runtime\RuntimeSuperchargerEvent;

$dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'runtime-feed-sim';
@mkdir($dir, 0775, true);

$path = $dir . DIRECTORY_SEPARATOR . 'runtime-supercharger-feed.ndjson';
$sink = new RuntimeNdjsonFileSink($path, 1024 * 1024, 3);

$event1 = new RuntimeSuperchargerEvent('runtime.reset', ['resetReport' => ['count' => 1]]);
$sink->emit($event1->toArray());

$event2 = new RuntimeSuperchargerEvent('runtime.workerDecision', ['decision' => ['shouldRecycle' => false, 'reason' => 'ok']]);
$sink->emit($event2->toArray());

fwrite(STDOUT, "written: " . $path . "\n");
