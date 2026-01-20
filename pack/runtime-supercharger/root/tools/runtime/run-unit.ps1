# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$ErrorActionPreference = "Stop"

$php = (Get-Command php -ErrorAction SilentlyContinue)
if (-not $php) {
  Write-Host "php is required on PATH."
  exit 2
}

$root = Split-Path -Parent $PSScriptRoot
$script = Join-Path $root "verify-bundle.php"

php $script
exit $LASTEXITCODE
