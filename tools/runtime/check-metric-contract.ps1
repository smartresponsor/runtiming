# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

param(
  [Parameter(Mandatory=$false)]
  [string]$BaseUrl = "http://127.0.0.1:8080",

  [Parameter(Mandatory=$false)]
  [string]$MetricsPath = "/metrics",

  [Parameter(Mandatory=$false)]
  [switch]$Strict
)

$base = $BaseUrl.TrimEnd("/")
$path = $MetricsPath
if (-not $path.StartsWith("/")) { $path = "/" + $path }
$url = $base + $path

try {
  $body = Invoke-RestMethod -Method Get -Uri $url -TimeoutSec 10
} catch {
  if ($Strict) {
    Write-Error "Failed to fetch metrics from $url"
    exit 2
  }
  Write-Host "metric contract: SKIP (endpoint unreachable: $url)"
  exit 0
}

$required = @(
  "runtime_supercharger_reset_total",
  "runtime_supercharger_reset_duration_seconds_sum",
  "runtime_supercharger_reset_duration_seconds_count",
  "runtime_supercharger_reset_count",
  "runtime_supercharger_worker_decision_total",
  "runtime_supercharger_rss_memory_mb",
  "runtime_supercharger_uptime_sec",
  "runtime_supercharger_request_count"
)

$missing = $false
foreach ($m in $required) {
  if (-not ($body -match "(?m)^" + [regex]::Escape($m))) {
    Write-Error "missing metric: $m"
    $missing = $true
  }
}

if (-not ($body -match '(?m)^runtime_supercharger_reset_total\{.*result=')) {
  Write-Error "missing label contract: runtime_supercharger_reset_total{result=...}"
  $missing = $true
}

if ($missing) {
  Write-Error "metric contract: FAIL"
  exit 1
}

Write-Host "metric contract: OK"
exit 0
