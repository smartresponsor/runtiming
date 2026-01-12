<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

// Optional smoke (requires vendor/autoload.php in integrated repo).

$root = dirname(__DIR__, 2);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDOUT, "ok (no vendor/autoload.php)\n");
    exit(0);
}

require_once $autoload;

use App\Service\Runtime\RuntimeResetRegistry;
use App\ServiceInterface\Runtime\RuntimeResetReport;
use App\ServiceInterface\Runtime\RuntimeResetterInterface;

final class DummyResetter implements RuntimeResetterInterface
{
    public int $count = 0;

    public function reset(RuntimeResetReport $report): void
    {
        $this->count++;
    }
}

$r = new DummyResetter();
$reg = new RuntimeResetRegistry([$r]);

$rep = $reg->resetAll();
if ($r->count !== 1) {
    fwrite(STDERR, "fail: reset not called\n");
    exit(1);
}
if ($rep->resetCount !== 1) {
    fwrite(STDERR, "fail: report resetCount\n");
    exit(1);
}

fwrite(STDOUT, "ok\n");
exit(0);
