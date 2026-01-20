# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Quick smoke for long-running mode: makes N requests and prints status.
# Usage: ./tools/runtime/smoke-engine.ps1 -Url http://localhost:8080/ -Count 25

param(
  [Parameter(Mandatory=$true)][string]$Url,
  [int]$Count = 25
)

$ErrorActionPreference = "Stop"

for ($i=1; $i -le $Count; $i++) {
  try {
    $r = Invoke-WebRequest -Uri $Url -Method GET -UseBasicParsing -TimeoutSec 10
    Write-Host ("[0/1] 2" -f $i,$Count,$r.StatusCode)
  } catch {
    Write-Host ("[0/1] ERROR 2" -f $i,$Count,$_.Exception.Message)
    exit 1
  }
}
