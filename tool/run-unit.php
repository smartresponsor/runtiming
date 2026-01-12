<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

require_once __DIR__ . '/../src/ServiceInterface/Runtime/RuntimeStatusSnapshot.php';
require_once __DIR__ . '/../src/ServiceInterface/Runtime/RuntimeStatusProviderInterface.php';
require_once __DIR__ . '/../src/Service/Runtime/RuntimeStatusProvider.php';
require_once __DIR__ . '/../src/Service/Runtime/RuntimeTelemetryDirInspector.php';

use App\Service\Runtime\RuntimeStatusProvider;
use App\Service\Runtime\RuntimeTelemetryDirInspector;

final class Unit
{
    private int $ok = 0;
    private int $fail = 0;

    public function run(): int
    {
        $this->caseProviderBasics();
        $this->caseInspectorMissingDir();
        $this->caseInspectorCounts();
        fwrite(STDOUT, "ok={$this->ok} fail={$this->fail}\n");
        return $this->fail > 0 ? 1 : 0;
    }

    private function assertTrue(bool $v, string $m): void
    {
        if (!$v) {
            $this->fail++;
            fwrite(STDERR, "FAIL: $m\n");
            return;
        }
        $this->ok++;
    }

    private function caseProviderBasics(): void
    {
        $t = 100.0;
        $now = function () use (&$t): float { return $t; };
        $p = new RuntimeStatusProvider('var/runtime/telemetry', $now);

        $s1 = $p->snapshot();
        $t += 2.0;
        $s2 = $p->snapshot();

        $this->assertTrue($s2->workerUptime >= $s1->workerUptime, 'uptime increases');
        $this->assertTrue($s2->php !== '', 'php version present');
    }

    private function caseInspectorMissingDir(): void
    {
        $i = new RuntimeTelemetryDirInspector('var/runtime/dir-not-exist-' . (string) random_int(1000, 9999));
        $r = $i->inspect();
        $this->assertTrue(($r['ready'] ?? null) === false, 'missing dir is not ready');
        $this->assertTrue(($r['workerSnapshotCount'] ?? 99) === 0, 'missing dir has zero count');
    }

    private function caseInspectorCounts(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'runtime-sketch-24-' . (string) random_int(1000, 9999);
        @mkdir($dir, 0775, true);

        file_put_contents($dir . '/w1.json', '{}');
        usleep(1000);
        file_put_contents($dir . '/w2.json', '{}');

        $i = new RuntimeTelemetryDirInspector($dir);
        $r = $i->inspect();

        $this->assertTrue(($r['ready'] ?? null) === true, 'existing dir is ready');
        $this->assertTrue(($r['workerSnapshotCount'] ?? 0) === 2, 'counts json files');
        $this->assertTrue((int) ($r['lastSnapshotUnix'] ?? 0) > 0, 'last snapshot time present');
    }
}

exit((new Unit())->run());
