# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

param(
  [string]$TargetUrl = "http://127.0.0.1:8080"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$Root = Resolve-Path (Join-Path $PSScriptRoot "..\..\..")
Set-Location $Root

$ts = (Get-Date).ToUniversalTime().ToString("yyyyMMddTHHmmssZ")
$out = Join-Path $Root "report\runtime\evidence\$ts"
New-Item -ItemType Directory -Force -Path $out | Out-Null

Write-Host "evidence: target=$TargetUrl"
Write-Host "evidence: out=$out"

function Try-HttpGet([string]$Url, [string]$Path) {
  try {
    Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 10 | Select-Object -ExpandProperty Content | Out-File -FilePath $Path -Encoding utf8
  } catch {
    "" | Out-File -FilePath $Path -Encoding utf8
  }
}

Try-HttpGet "$TargetUrl/status" (Join-Path $out "status.json")
Try-HttpGet "$TargetUrl/metrics" (Join-Path $out "metrics.prom")

Write-Host "evidence: run static ci gate"
& pwsh -NoProfile -ExecutionPolicy Bypass -File "tools/runtime/ci-gate.ps1" *> (Join-Path $out "ci-gate.txt")

if (Test-Path "tools/runtime/gate/run-rc-gate.ps1") {
  Write-Host "evidence: run rc gate (if host is up)"
  try {
    $env:RUNTIME_GATE_BASE_URL = $TargetUrl
    & pwsh -NoProfile -ExecutionPolicy Bypass -File "tools/runtime/gate/run-rc-gate.ps1" *> (Join-Path $out "rc-gate.txt")
  } catch {
    $_ | Out-String | Out-File (Join-Path $out "rc-gate.txt") -Encoding utf8
  }
}

if (Get-Command k6 -ErrorAction SilentlyContinue) {
  Write-Host "evidence: run k6 baseline"
  try {
    & pwsh -NoProfile -ExecutionPolicy Bypass -File "tools/k6/run-k6-runtime.ps1" baseline $TargetUrl *> (Join-Path $out "k6-baseline.txt")
  } catch {
    $_ | Out-String | Out-File (Join-Path $out "k6-baseline.txt") -Encoding utf8
  }
}

@"
Runtime Supercharger evidence bundle

utc_timestamp=$ts
target_url=$TargetUrl

files:
- status.json (optional)
- metrics.prom (optional)
- ci-gate.txt
- rc-gate.txt (optional)
- k6-baseline.txt (optional)
"@ | Out-File -FilePath (Join-Path $out "MANIFEST.txt") -Encoding utf8

Write-Host "evidence: done"
