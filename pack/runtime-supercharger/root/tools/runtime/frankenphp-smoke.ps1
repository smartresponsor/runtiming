# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$ErrorActionPreference = "Stop"

function Require-Command($name) {
  $cmd = Get-Command $name -ErrorAction SilentlyContinue
  if (-not $cmd) { throw "Missing command: $name" }
}

Require-Command "php"
Require-Command "frankenphp"

$root = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$worker = Join-Path $root "worker\frankenphp-worker.php"

Write-Host "Starting FrankenPHP with worker: $worker"
$proc = Start-Process -FilePath "frankenphp" -ArgumentList @("php-server", "--worker", $worker, "-l", "127.0.0.1:8080") -PassThru -NoNewWindow

Start-Sleep -Seconds 2

$base = "http://127.0.0.1:8080"

function Get-Url($u) {
  try {
    $r = Invoke-WebRequest -Uri $u -UseBasicParsing -TimeoutSec 5
    return $r.StatusCode
  } catch {
    return 0
  }
}

$code1 = Get-Url "$base/status"
$code2 = Get-Url "$base/metrics"

Write-Host "/status=$code1 /metrics=$code2"

if ($code1 -lt 200 -or $code1 -ge 600) { throw "/status failed ($code1). Did you import runtime_status routes (sketch-24)?" }
if ($code2 -lt 200 -or $code2 -ge 600) { throw "/metrics failed ($code2). Did you import runtime_telemetry routes (sketch-23)?" }

$target = "$base/status"
$N = 200
$sw = [System.Diagnostics.Stopwatch]::StartNew()
for ($i=0; $i -lt $N; $i++) {
  $c = Get-Url $target
  if ($c -ne 200) { throw "bench failed at i=$i code=$c" }
}
$sw.Stop()
$ms = $sw.ElapsedMilliseconds
Write-Host "bench ok: $N requests in ${ms}ms"

Write-Host "Stopping FrankenPHP"
try {
  Stop-Process -Id $proc.Id -Force
} catch {
  Write-Host "Stop-Process failed: $($_.Exception.Message)"
}

exit 0
