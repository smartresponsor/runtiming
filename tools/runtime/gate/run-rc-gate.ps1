# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$ErrorActionPreference = "Stop"

$BaseUrl = $env:RUNTIME_GATE_BASE_URL
if ([string]::IsNullOrWhiteSpace($BaseUrl)) { $BaseUrl = "http://127.0.0.1:8080" }

$RouteList = $env:RUNTIME_GATE_ROUTE_LIST
if ([string]::IsNullOrWhiteSpace($RouteList)) { $RouteList = "/status" }

$Mode = $env:RUNTIME_GATE_MODE
if ([string]::IsNullOrWhiteSpace($Mode)) { $Mode = "read" }

if ([string]::IsNullOrWhiteSpace($env:RUNTIME_GATE_P95_MS_MAX)) {
  if ($Mode -eq "write") { $env:RUNTIME_GATE_P95_MS_MAX = "700" } else { $env:RUNTIME_GATE_P95_MS_MAX = "250" }
}
if ([string]::IsNullOrWhiteSpace($env:RUNTIME_GATE_FAIL_RATE_MAX)) {
  $env:RUNTIME_GATE_FAIL_RATE_MAX = "0.005"
}

$ProbeSecond = [int]($env:RUNTIME_GATE_PROBE_SECOND)
if ($ProbeSecond -le 0) { $ProbeSecond = 15 }
$Concurrency = [int]($env:RUNTIME_GATE_CONCURRENCY)
if ($Concurrency -le 0) { $Concurrency = 8 }

$Token = $env:RUNTIME_GATE_TOKEN
$TokenHeader = $env:RUNTIME_GATE_TOKEN_HEADER
if ([string]::IsNullOrWhiteSpace($TokenHeader)) { $TokenHeader = "X-Runtime-Token" }

$RunBench = $env:RUNTIME_GATE_RUN_BENCH
if ([string]::IsNullOrWhiteSpace($RunBench)) { $RunBench = "1" }

$stamp = Get-Date -Format "yyyyMMddTHHmmss"
$root = Split-Path -Parent $PSScriptRoot | Split-Path -Parent | Split-Path -Parent | Split-Path -Parent
$outDir = Join-Path $root ("report/runtime/gate/" + $stamp)
New-Item -ItemType Directory -Force -Path $outDir | Out-Null
$routeDir = Join-Path $outDir "route"
New-Item -ItemType Directory -Force -Path $routeDir | Out-Null

$logPath = Join-Path $outDir "gate.log"
"stamp=$stamp" | Set-Content -Path $logPath -Encoding utf8

function Log([string]$s) {
  $s | Add-Content -Path $logPath -Encoding utf8
  Write-Host $s
}

# platform snapshot
$plat = Join-Path $PSScriptRoot "check-platform.ps1"
$platJson = Join-Path $outDir "platform.json"
& powershell -ExecutionPolicy Bypass -File $plat | Set-Content -Path $platJson -Encoding utf8

# verify dependency verifiers (strict)
$verifyList = @(
  "tool/runtime/verify-roadrunner-adapter.php",
  "tool/runtime/verify-bench-soak.php",
  "tool/runtime/verify-frankenphp-adapter.php",
  "tool/runtime/verify-swoole-adapter.php",
  "tool/runtime/verify-prod-runbook-pack.php"
)

foreach ($rel in $verifyList) {
  $p = Join-Path $root $rel
  if (-not (Test-Path $p)) {
    Log ("missing dependency verify: " + $rel)
    exit 1
  }
  Log ("verify: " + $rel)
  & php $p | Out-Null
  if ($LASTEXITCODE -ne 0) {
    Log ("verify failed: " + $rel)
    exit 1
  }
}

# multi-route probe
$probe = Join-Path $PSScriptRoot "probe-route.ps1"
$eval = Join-Path $PSScriptRoot "evaluate-multi-gate.py"
$routes = $RouteList.Split(",") | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne "" }

$csvList = @()
foreach ($r in $routes) {
  $key = ($r -replace '[^a-zA-Z0-9]+','-').Trim('-')
  if ([string]::IsNullOrWhiteSpace($key)) { $key = "route" }
  $dir = Join-Path $routeDir $key
  New-Item -ItemType Directory -Force -Path $dir | Out-Null
  $csv = Join-Path $dir "probe.csv"
  Log ("probe: " + $r)
  & powershell -ExecutionPolicy Bypass -File $probe -BaseUrl $BaseUrl -Route $r -ProbeSecond $ProbeSecond -Concurrency $Concurrency -OutCsv $csv -Token $Token -TokenHeader $TokenHeader | Out-Null
  $csvList += $csv
}

# evaluate combined
$gateJson = Join-Path $outDir "gate.json"
Log "gate: evaluate (multi-route)"
$gateOut = & python $eval @csvList
$gateOut | Set-Content -Path $gateJson -Encoding utf8

# write per-route eval.json for convenience
try {
  $gateObj = $gateOut | ConvertFrom-Json
  foreach ($item in $gateObj.route) {
    $file = $item.file
    if (Test-Path $file) {
      $dir = Split-Path -Parent $file
      ($item | ConvertTo-Json -Depth 8) | Set-Content -Path (Join-Path $dir "eval.json") -Encoding utf8
    }
  }
} catch { }

# optional bench harness (extra safety)
if ($RunBench -eq "1") {
  $bench = Join-Path $root "tools/runtime/bench/run-bench.ps1"
  if (Test-Path $bench) {
    Log "bench: start (optional)"
    & powershell -ExecutionPolicy Bypass -File $bench | Out-Null
    $benchRoot = Join-Path $root "report/runtime/bench"
    if (Test-Path $benchRoot) {
      $latest = Get-ChildItem -Directory $benchRoot | Sort-Object Name -Descending | Select-Object -First 1
      if ($latest) {
        if (Test-Path (Join-Path $latest.FullName "summary.json")) { Copy-Item (Join-Path $latest.FullName "summary.json") (Join-Path $outDir "bench-summary.json") -Force }
        if (Test-Path (Join-Path $latest.FullName "status.csv")) { Copy-Item (Join-Path $latest.FullName "status.csv") (Join-Path $outDir "bench-status.csv") -Force }
        if (Test-Path (Join-Path $latest.FullName "metrics.json")) { Copy-Item (Join-Path $latest.FullName "metrics.json") (Join-Path $outDir "bench-metrics.json") -Force }
      }
    }
  } else {
    Log "bench skipped: dependency tools/runtime/bench/run-bench.ps1 not found"
  }
}

# decision
$pass = $false
try {
  $gateObj2 = Get-Content -Raw -Path $gateJson | ConvertFrom-Json
  if ($gateObj2.pass -eq $true) { $pass = $true }
} catch { }

if ($pass) { Log "PASS"; exit 0 }
Log "FAIL"; exit 1
