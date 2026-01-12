<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeNdjsonSinkInterface.php';
require_once __DIR__ . '/../../src/ServiceInterface/Runtime/RuntimeSuperchargerEvent.php';

require_once __DIR__ . '/../../src/Service/Runtime/RuntimeNdjsonRotator.php';
require_once __DIR__ . '/../../src/Service/Runtime/RuntimeNdjsonFileSink.php';

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
