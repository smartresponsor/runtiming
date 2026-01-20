# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
param(
  [string]$BaseUrl,
  [string]$Route,
  [int]$ProbeSecond,
  [int]$Concurrency,
  [string]$OutCsv,
  [string]$Token,
  [string]$TokenHeader
)
$ErrorActionPreference = "Stop"
if ([string]::IsNullOrWhiteSpace($TokenHeader)) { $TokenHeader = "X-Runtime-Token" }

"ts,http_code,ms,bytes" | Set-Content -Path $OutCsv -Encoding utf8

function Invoke-One {
  param([string]$Url, [string]$Token, [string]$TokenHeader)
  $headers = @{}
  if (-not [string]::IsNullOrWhiteSpace($Token)) { $headers[$TokenHeader] = $Token }
  $sw = [System.Diagnostics.Stopwatch]::StartNew()
  try {
    $r = Invoke-WebRequest -UseBasicParsing -Uri $Url -Headers $headers -TimeoutSec 10
    $sw.Stop()
    $ms = $sw.ElapsedMilliseconds
    $bytes = 0
    if ($r.Content) { $bytes = ([System.Text.Encoding]::UTF8.GetByteCount($r.Content)) }
    return @($r.StatusCode, $ms, $bytes)
  } catch {
    $sw.Stop()
    return @(0, $sw.ElapsedMilliseconds, 0)
  }
}

$endAt = (Get-Date).AddSeconds($ProbeSecond)
$full = $BaseUrl.TrimEnd("/") + $Route

$workers = @()
for ($i=0; $i -lt $Concurrency; $i++) {
  $workers += Start-Job -ScriptBlock {
    param($endAt, $full, $token, $tokenHeader, $outCsv)
    while ((Get-Date) -lt $endAt) {
      $ts = [int][double]::Parse((Get-Date -UFormat %s))
      $headers = @{}
      if (-not [string]::IsNullOrWhiteSpace($token)) { $headers[$tokenHeader] = $token }
      $sw = [System.Diagnostics.Stopwatch]::StartNew()
      try {
        $r = Invoke-WebRequest -UseBasicParsing -Uri $full -Headers $headers -TimeoutSec 10
        $sw.Stop()
        $ms = $sw.ElapsedMilliseconds
        $bytes = 0
        if ($r.Content) { $bytes = ([System.Text.Encoding]::UTF8.GetByteCount($r.Content)) }
        $line = "{0},{1},{2},{3}" -f $ts, $r.StatusCode, $ms, $bytes
      } catch {
        $sw.Stop()
        $line = "{0},{1},{2},{3}" -f $ts, 0, $sw.ElapsedMilliseconds, 0
      }
      Add-Content -Path $outCsv -Value $line -Encoding utf8
    }
  } -ArgumentList @($endAt, $full, $Token, $TokenHeader, $OutCsv)
}

Wait-Job -Job $workers | Out-Null
$workers | Remove-Job | Out-Null
