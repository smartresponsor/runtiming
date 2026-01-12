Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$ErrorActionPreference = "Stop"

function Find-Frankenphp {
  $bin = Get-Command frankenphp -ErrorAction SilentlyContinue
  if ($bin) { return @("frankenphp", @("run")) }
  $caddy = Get-Command caddy -ErrorAction SilentlyContinue
  if ($caddy) { return @("caddy", @("run")) }
  $local = Join-Path $PSScriptRoot "frankenphp.exe"
  if (Test-Path $local) { return @($local, @("run")) }
  $localCaddy = Join-Path $PSScriptRoot "caddy.exe"
  if (Test-Path $localCaddy) { return @($localCaddy, @("run")) }
  throw "frankenphp/caddy binary not found (install FrankenPHP or Caddy with FrankenPHP module, or place binary in tools/frankenphp/)"
}

$root = Split-Path -Parent $PSScriptRoot | Split-Path -Parent | Split-Path -Parent
$caddyfile = Join-Path $root "Caddyfile"
if (-not (Test-Path $caddyfile)) {
  Copy-Item (Join-Path $PSScriptRoot "template\\Caddyfile") $caddyfile -Force
  Write-Host "created Caddyfile from template"
}

$cmd = Find-Frankenphp
$exe = $cmd[0]
$args = $cmd[1]

# Prefer explicit config path.
if ($exe -eq "frankenphp" -or $exe -like "*frankenphp*") {
  & $exe @args "--config" $caddyfile
} else {
  & $exe @args "--config" $caddyfile
}
