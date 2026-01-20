<?php
declare(strict_types=1);



namespace App\Service\Runtime;

final class RuntimeTelemetryDirInspector
{
    private string $dir;

    public function __construct(string $dir = 'var/runtime/telemetry')
    {
        $this->dir = $dir !== '' ? $dir : 'var/runtime/telemetry';
    }

    /** @return array<string,mixed> */
    public function inspect(): array
    {
        $dir = $this->dir;

        if (!is_dir($dir)) {
            return [
                'dir' => $dir,
                'ready' => false,
                'reason' => 'dirMissing',
                'workerSnapshotCount' => 0,
                'lastSnapshotUnix' => 0,
            ];
        }

        $count = 0;
        $last = 0;

        $h = @opendir($dir);
        if (!is_resource($h)) {
            return [
                'dir' => $dir,
                'ready' => false,
                'reason' => 'dirNotReadable',
                'workerSnapshotCount' => 0,
                'lastSnapshotUnix' => 0,
            ];
        }

        while (($name = readdir($h)) !== false) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            if (!str_ends_with($name, '.json')) {
                continue;
            }

            $count++;
            $p = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
            $mt = (int) @filemtime($p);
            if ($mt > $last) {
                $last = $mt;
            }
        }

        closedir($h);

        return [
            'dir' => $dir,
            'ready' => true,
            'reason' => 'ok',
            'workerSnapshotCount' => $count,
            'lastSnapshotUnix' => $last,
        ];
    }
}
