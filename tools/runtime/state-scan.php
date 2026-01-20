<?php

    // Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

    declare(strict_types=1);


    final class RuntimeStateScan
    {
        public const VERSION = '01_0';

        /** @var string[] */
        private array $includeDir;
        /** @var string[] */
        private array $excludeDir;

        private string $root;
        private string $failOn;

        /** @var array<int, array<string, mixed>> */
        private array $finding = [];

        public function __construct(string $root, array $includeDir, array $excludeDir, string $failOn)
        {
            $this->root = rtrim($root, "/\\");
            $this->includeDir = $includeDir;
            $this->excludeDir = $excludeDir;
            $this->failOn = $failOn;
        }

        public static function main(array $argv): int
        {
            $opt = self::parseArg($argv);

            $root = $opt['root'] ?? getcwd();
            $include = self::splitList($opt['include'] ?? 'src,config');
            $exclude = self::splitList($opt['exclude'] ?? 'vendor,var,node_modules,public,tests,test');
            $failOn = strtolower(trim($opt['failOn'] ?? 'error'));

            if (!in_array($failOn, ['none','error','warning'], true)) {
                fwrite(STDERR, "Invalid --fail-on. Use: none|error|warning\n");
                return 2;
            }

            $scan = new self($root, $include, $exclude, $failOn);
            $scan->run();
            $scan->writeReport();

            return $scan->exitCode();
        }

        private static function parseArg(array $argv): array
        {
            $out = [];
            foreach ($argv as $i => $a) {
                if ($i === 0) continue;
                if (strpos($a, '--') !== 0) continue;
                $eq = strpos($a, '=');
                if ($eq === false) {
                    $key = substr($a, 2);
                    $out[$key] = '1';
                    continue;
                }
                $key = substr($a, 2, $eq - 2);
                $val = substr($a, $eq + 1);
                $out[$key] = $val;
            }
            return $out;
        }

        /** @return string[] */
        private static function splitList(string $s): array
        {
            $parts = array_map('trim', explode(',', $s));
            $parts = array_values(array_filter($parts, static fn($v) => $v !== ''));
            return $parts;
        }

        private function run(): void
        {
            $files = $this->collectPhpFile();
            foreach ($files as $file) {
                $this->scanFile($file);
            }
        }

        /** @return string[] */
        private function collectPhpFile(): array
        {
            $result = [];
            foreach ($this->includeDir as $dir) {
                $path = $this->root . DIRECTORY_SEPARATOR . $dir;
                if (!is_dir($path)) {
                    continue;
                }
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
                );
                foreach ($it as $info) {
                    if (!$info->isFile()) continue;
                    $p = $info->getPathname();
                    if (!$this->isPhp($p)) continue;
                    if ($this->isExcluded($p)) continue;
                    $result[] = $p;
                }
            }
            sort($result);
            return $result;
        }

        private function isPhp(string $path): bool
        {
            return (bool)preg_match('/\.php$/i', $path);
        }

        private function isExcluded(string $path): bool
        {
            $norm = str_replace('\\', '/', $path);
            foreach ($this->excludeDir as $ex) {
                $ex = trim($ex);
                if ($ex === '') continue;
                if (strpos($norm, '/' . $ex . '/') !== false) {
                    return true;
                }
            }
            return false;
        }

        private function scanFile(string $path): void
        {
            $content = @file_get_contents($path);
            if ($content === false) {
                return;
            }

            $rel = $this->relPath($path);

            $this->scanRegex($rel, $content, 'RS001', 'warning',
                '/\b(public|protected|private)\s+static\s+\$[A-Za-z_][A-Za-z0-9_]*\b/',
                'Static property in class may leak state across requests.'
            );

            $this->scanRegex($rel, $content, 'RS002', 'warning',
                '/\bprivate\s+static\s+\$instance\b|\bgetInstance\s*\(/',
                'Singleton pattern may create shared mutable state across requests.'
            );

            $this->scanRegex($rel, $content, 'RS003', 'warning',
                '/\$_SESSION\b|\$_SERVER\b|\$_ENV\b|\$_COOKIE\b/',
                'Superglobals usage requires strict request-boundary discipline.'
            );

            $this->scanRegex($rel, $content, 'RS004', 'warning',
                '/\bfunction\s+__destruct\s*\(/',
                'Destructor detected; review for resource leakage in long-living workers.'
            );

            $this->scanRegex($rel, $content, 'RS005', 'warning',
                '/\b(private|protected|public)\s+(array|iterable)\s+\$[A-Za-z0-9_]*(cache|registry|buffer|map)[A-Za-z0-9_]*\b/i',
                'Heuristic: in-memory buffer/cache may retain cross-request data. Ensure reset.'
            );
        }

        private function scanRegex(string $rel, string $content, string $ruleId, string $severity, string $regex, string $message): void
        {
            if (!preg_match_all($regex, $content, $m, PREG_OFFSET_CAPTURE)) {
                return;
            }
            foreach ($m[0] as $hit) {
                $text = (string)$hit[0];
                $pos = (int)$hit[1];
                $line = 1 + substr_count(substr($content, 0, $pos), "\n");
                $excerpt = $this->lineExcerpt($content, $line);
                $this->finding[] = [
                    'ruleId' => $ruleId,
                    'severity' => $severity,
                    'file' => $rel,
                    'line' => $line,
                    'message' => $message,
                    'match' => $text,
                    'excerpt' => $excerpt,
                ];
            }
        }

        private function lineExcerpt(string $content, int $line): string
        {
            $lines = preg_split("/\R/", $content) ?: [];
            $idx = max(0, $line - 1);
            return isset($lines[$idx]) ? trim($lines[$idx]) : '';
        }

        private function relPath(string $path): string
        {
            $root = str_replace('\\', '/', $this->root);
            $p = str_replace('\\', '/', $path);
            if (strpos($p, $root . '/') === 0) {
                return substr($p, strlen($root) + 1);
            }
            return $p;
        }

        private function writeReport(): void
        {
            $outDir = $this->root . DIRECTORY_SEPARATOR . 'report' . DIRECTORY_SEPARATOR . 'runtime';
            if (!is_dir($outDir)) {
                @mkdir($outDir, 0777, true);
            }

            $ndjsonPath = $outDir . DIRECTORY_SEPARATOR . 'runtime-state-scan-report.ndjson';
            $summaryPath = $outDir . DIRECTORY_SEPARATOR . 'runtime-state-scan-summary.json';

            $fh = fopen($ndjsonPath, 'wb');
            if ($fh === false) {
                fwrite(STDERR, "Failed to write report: $ndjsonPath\n");
                return;
            }
            foreach ($this->finding as $f) {
                fwrite($fh, json_encode($f, JSON_UNESCAPED_SLASHES) . "\n");
            }
            fclose($fh);

            $count = ['warning'=>0,'error'=>0];
            foreach ($this->finding as $f) {
                $sev = (string)($f['severity'] ?? 'warning');
                if (!isset($count[$sev])) {
                    $count[$sev] = 0;
                }
                $count[$sev]++;
            }

            $summary = [
                'version' => self::VERSION,
                'created_utc' => gmdate('c') . 'Z',
                'root' => $this->root,
                'failOn' => $this->failOn,
                'findingCount' => $count,
                'total' => count($this->finding),
            ];

            file_put_contents($summaryPath, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

            fwrite(STDOUT, "State scan done. total=" . $summary['total'] . " warning=" . $count['warning'] . " error=" . $count['error'] . "\n");
            fwrite(STDOUT, "Report: report/runtime/runtime-state-scan-report.ndjson\n");
        }

        private function exitCode(): int
        {
            if ($this->failOn === 'none') {
                return 0;
            }

            $hasWarning = false;
            $hasError = false;

            foreach ($this->finding as $f) {
                $sev = (string)($f['severity'] ?? 'warning');
                if ($sev === 'error') $hasError = true;
                if ($sev === 'warning') $hasWarning = true;
            }

            if ($this->failOn === 'error') {
                return $hasError ? 1 : 0;
            }

            if ($this->failOn === 'warning') {
                return ($hasError || $hasWarning) ? 1 : 0;
            }

            return 0;
        }
    }

    exit(RuntimeStateScan::main($argv));
