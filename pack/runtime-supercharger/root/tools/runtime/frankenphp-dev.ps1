# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
# Start FrankenPHP worker-mode using Docker Compose.
# Run from your Symfony project root.

$ErrorActionPreference = "Stop"

if (!(Test-Path -LiteralPath "docker-compose.yml")) {
  Write-Host "docker-compose.yml not found in project root. Copy config/runtime/frankenphp/docker-compose.yml to ./docker-compose.yml or adjust the path."
  exit 2
}

docker compose up --build
