# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

$ErrorActionPreference = 'Stop'

Write-Host "ci-gate: php"
php -v

if (-Not (Test-Path -Path "composer.json")) {
  throw "ci-gate: missing composer.json"
}

Write-Host "ci-gate: composer install"
composer install --no-interaction --prefer-dist --no-progress

Write-Host "ci-gate: verify bundle pack"
php tools/runtime/verify-bundle.php

Write-Host "ci-gate: verify endpoint security pack"
php tools/runtime/verify-endpoint-security.php

Write-Host "ci-gate: verify worker lifecycle pack"
php tools/runtime/verify-worker-lifecycle.php

Write-Host "ci-gate: verify worker reset pack"
php tools/runtime/verify-worker-reset.php

Write-Host "ci-gate: metric contract"
pwsh tools/runtime/check-metric-contract.ps1

Write-Host "ci-gate: phpunit"
vendor/bin/phpunit -c phpunit.xml.dist

Write-Host "ci-gate: phpstan"
vendor/bin/phpstan analyse -c phpstan.neon

Write-Host "ci-gate: ok"
