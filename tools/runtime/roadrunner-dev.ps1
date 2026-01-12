# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Start RoadRunner using vendor/bin/rr (provided by runtime/roadrunner-symfony-nyholm deps).
# Run from your Symfony project root.

$ErrorActionPreference = "Stop"

$rr = Join-Path -Path "vendor" -ChildPath "bin/rr"
if (!(Test-Path -LiteralPath $rr)) {
  Write-Host "vendor/bin/rr not found. Ensure: composer require runtime/roadrunner-symfony-nyholm"
  exit 2
}

$config = "config/runtime/roadrunner/.rr.yaml"
if (!(Test-Path -LiteralPath $config)) {
  Write-Host "Config not found: $config"
  exit 2
}

& $rr serve -c $config
