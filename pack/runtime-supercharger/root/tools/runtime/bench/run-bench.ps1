# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$ErrorActionPreference = "Stop"

$BaseUrl = $env:RUNTIME_BENCH_BASE_URL
if ([string]::IsNullOrWhiteSpace($BaseUrl)) { $BaseUrl = "http://127.0.0.1:8080" }

$Route = $env:RUNTIME_BENCH_ROUTE
if ([string]::IsNullOrWhiteSpace($Route)) { $Route = "/status" }

$WarmupCount = [int]($env:RUNTIME_BENCH_WARMUP_COUNT)
if ($WarmupCount -le 0) { $WarmupCount = 50 }

$LoadSecond = [int]($env:RUNTIME_BENCH_LOAD_SECOND)
if ($LoadSecond -le 0) { $LoadSecond = 30 }

$SoakMinute = [int]($env:RUNTIME_BENCH_SOAK_MINUTE)
if ($SoakMinute -le 0) { $SoakMinute = 10 }

$Concurrency = [int]($env:RUNTIME_BENCH_CONCURRENCY)
if ($Concurrency -le 0) { $Concurrency = 8 }

$SampleSecond = [int]($env:RUNTIME_BENCH_SAMPLE_SECOND)
if ($SampleSecond -le 0) { $SampleSecond = 5 }

$Token = $env:RUNTIME_BENCH_TOKEN
$TokenHeader = $env:RUNTIME_BENCH_TOKEN_HEADER
if ([string]::IsNullOrWhiteSpace($TokenHeader)) { $TokenHeader = "X-Runtime-Token" }

$stamp = Get-Date -Format "yyyyMMddTHHmmss"
$root = Split-Path -Parent $PSScriptRoot | Split-Path -Parent | Split-Path -Parent
$outDir = Join-Path $root ("report/runtime/bench/" + $stamp)
New-Item -ItemType Directory -Force -Path $outDir | Out-Null

function Invoke-Get {
  param([string]$Url)
  $headers = @{}
  if (-not [string]::IsNullOrWhiteSpace($Token)) {
    $headers[$TokenHeader] = $Token
  }
  try {
    return Invoke-WebRequest -UseBasicParsing -Uri $Url -Headers $headers -TimeoutSec 10
  } catch {
    return $null
  }
}

# warmup
for ($i=0; $i -lt $WarmupCount; $i++) {
  $r = Invoke-Get ($BaseUrl + $Route)
  Start-Sleep -Milliseconds 20
}

# start status sampler (background job)
$sampleScript = Join-Path $PSScriptRoot "sample-status.ps1"
$job = Start-Job -FilePath $sampleScript -ArgumentList @($BaseUrl, $outDir, $SampleSecond, $Token, $TokenHeader)

# load phase (simple parallel jobs of curl loop)
$endAt = (Get-Date).AddSeconds($LoadSecond)

$workers = @()
for ($c=0; $c -lt $Concurrency; $c++) {
  $workers += Start-Job -ScriptBlock {
    param($BaseUrl, $Route, $endAt, $Token, $TokenHeader)
    while ((Get-Date) -lt $endAt) {
      $headers = @()
      if (-not [string]::IsNullOrWhiteSpace($Token)) {
        $headers = @("-H", ($TokenHeader + ": " + $Token))
      }
      $null = & curl.exe -s -o NUL -w "%{http_code}" @headers ($BaseUrl + $Route) 2>$null
    }
  } -ArgumentList @($BaseUrl, $Route, $endAt, $Token, $TokenHeader)
}

Wait-Job -Job $workers | Out-Null
$workers | Remove-Job | Out-Null

# soak phase: just wait while sampler continues
Start-Sleep -Seconds ($SoakMinute * 60)

Stop-Job $job | Out-Null
Receive-Job $job | Out-Null
Remove-Job $job | Out-Null

# fetch /metrics and parse if available
$metricsUrl = $BaseUrl + "/metrics"
$m = Invoke-Get $metricsUrl
if ($m -ne $null -and $m.StatusCode -eq 200) {
  $raw = $m.Content
  $rawPath = Join-Path $outDir "metrics.prom"
  Set-Content -Path $rawPath -Value $raw -Encoding utf8

  $py = Join-Path $PSScriptRoot "parse-prom-metrics.py"
  $jsonPath = Join-Path $outDir "metrics.json"
  $raw | python $py | Set-Content -Path $jsonPath -Encoding utf8
}

# summary
$summary = @{
  stamp = $stamp
  baseUrl = $BaseUrl
  route = $Route
  warmupCount = $WarmupCount
  loadSecond = $LoadSecond
  soakMinute = $SoakMinute
  concurrency = $Concurrency
  sampleSecond = $SampleSecond
}

$summaryPath = Join-Path $outDir "summary.json"
($summary | ConvertTo-Json -Depth 8) | Set-Content -Path $summaryPath -Encoding utf8

Write-Host ("ok: " + $outDir)
