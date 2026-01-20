<?php
declare(strict_types=1);



$root = dirname(__DIR__, 2);

$expect = [
  'README.md',
  'docs/runtime/runtime-supercharger-rc-gate-route-02_0.md',
  'docs/runtime/runtime-supercharger-version-policy-01_0.md',
  'ops/runtime/runtime-supercharger-gate-target.yaml',
  'tools/runtime/gate/run-rc-gate.ps1',
  'tools/runtime/gate/run-rc-gate.sh',
  'tools/runtime/gate/check-platform.ps1',
  'tools/runtime/gate/check-platform.sh',
  'tools/runtime/gate/probe-route.ps1',
  'tools/runtime/gate/probe-route.sh',
  'tools/runtime/gate/evaluate-multi-gate.py',
];

$fail = 0;

foreach ($expect as $rel) {
  $p = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
  if (!is_file($p)) {
    fwrite(STDERR, "missing: $rel\n");
    $fail++;
    continue;
  }
  $s = file_get_contents($p);
  if (!is_string($s) || trim($s) === '') {
    fwrite(STDERR, "empty: $rel\n");
    $fail++;
  }
}

$needle = [
  'tools/runtime/gate/run-rc-gate.ps1' => ['RUNTIME_GATE_ROUTE_LIST', 'probe-route.ps1', 'evaluate-multi-gate.py'],
  'tools/runtime/gate/run-rc-gate.sh' => ['RUNTIME_GATE_ROUTE_LIST', 'probe-route.sh', 'evaluate-multi-gate.py'],
  'docs/runtime/runtime-supercharger-rc-gate-route-02_0.md' => ['PHP 9.5 does not exist', 'RUNTIME_GATE_ROUTE_LIST'],
];

foreach ($needle as $rel => $list) {
  $p = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
  $s = is_file($p) ? file_get_contents($p) : '';
  $s = is_string($s) ? $s : '';
  foreach ($list as $n) {
    if (strpos($s, $n) === false) {
      fwrite(STDERR, "check failed: $rel lacks '$n'\n");
      $fail++;
    }
  }
}

if ($fail > 0) {
  fwrite(STDERR, "fail=$fail\n");
  exit(1);
}

fwrite(STDOUT, "ok\n");
exit(0);
