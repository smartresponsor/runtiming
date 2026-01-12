Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
param(
  [string]$BaseUrl,
  [string]$OutDir,
  [int]$SampleSecond,
  [string]$Token,
  [string]$TokenHeader
)
$ErrorActionPreference = "Stop"
if ([string]::IsNullOrWhiteSpace($TokenHeader)) { $TokenHeader = "X-Runtime-Token" }

$path = Join-Path $OutDir "status.csv"
"ts,http_code,ms,bytes" | Set-Content -Path $path -Encoding utf8

function Invoke-Get {
  param([string]$Url)
  $headers = @{}
  if (-not [string]::IsNullOrWhiteSpace($Token)) {
    $headers[$TokenHeader] = $Token
  }
  $sw = [System.Diagnostics.Stopwatch]::StartNew()
  try {
    $r = Invoke-WebRequest -UseBasicParsing -Uri $Url -Headers $headers -TimeoutSec 10
    $sw.Stop()
    $ms = $sw.ElapsedMilliseconds
    $bytes = 0
    if ($r.Content) { $bytes = ([System.Text.Encoding]::UTF8.GetByteCount($r.Content)) }
    $line = "{0},{1},{2},{3}" -f ([int][double]::Parse((Get-Date -UFormat %s))), $r.StatusCode, $ms, $bytes
    Add-Content -Path $path -Value $line -Encoding utf8
  } catch {
    $sw.Stop()
    $line = "{0},{1},{2},{3}" -f ([int][double]::Parse((Get-Date -UFormat %s))), 0, $sw.ElapsedMilliseconds, 0
    Add-Content -Path $path -Value $line -Encoding utf8
  }
}

while ($true) {
  Invoke-Get ($BaseUrl + "/status")
  Start-Sleep -Seconds $SampleSecond
}
