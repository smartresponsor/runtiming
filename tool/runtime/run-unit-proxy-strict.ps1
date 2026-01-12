Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$ErrorActionPreference = "Stop"

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
  Write-Host "php is required on PATH."
  exit 2
}

$root = Split-Path -Parent $PSScriptRoot

php (Join-Path $PSScriptRoot "verify-proxy-strict.php")
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

# smoke requires vendor/autoload.php (run only in integrated repo)
$autoload = Join-Path (Split-Path -Parent $root) "vendor\autoload.php"
if (Test-Path $autoload) {
  php (Join-Path $PSScriptRoot "guard-smoke.php")
  exit $LASTEXITCODE
}

Write-Host "ok (verify only; no vendor/autoload.php)"
exit 0
