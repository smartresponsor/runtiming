# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
param(
  [Parameter(Mandatory=$false)][string]$Root = ".",
  [Parameter(Mandatory=$false)][ValidateSet("none","error","warning")][string]$FailOn = "error",
  [Parameter(Mandatory=$false)][string]$Include = "src,config",
  [Parameter(Mandatory=$false)][string]$Exclude = "vendor,var,node_modules,public,tests,test"
)

$ErrorActionPreference = "Stop"

$php = (Get-Command php -ErrorAction SilentlyContinue)
if (-not $php) {
  Write-Host "php is required on PATH."
  exit 2
}

$script = Join-Path $PSScriptRoot "state-scan.php"
if (-not (Test-Path $script)) {
  Write-Host "Missing state-scan.php at $script"
  exit 2
}

Write-Host "Runtime state scan: root=$Root failOn=$FailOn include=$Include exclude=$Exclude"
php $script --root=$Root --failOn=$FailOn --include=$Include --exclude=$Exclude
exit $LASTEXITCODE
