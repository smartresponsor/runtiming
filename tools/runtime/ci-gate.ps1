# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

$ErrorActionPreference = "Stop"

function Invoke-Checked {
  param(
    [string]$Label,
    [scriptblock]$Command
  )
  Write-Host $Label
  & $Command
  if ($LASTEXITCODE -ne 0) {
    throw "ci-gate: failed -> $Label (exit=$LASTEXITCODE)"
  }
}

Invoke-Checked "ci-gate: php" { php -v }

if (-Not (Test-Path -Path "composer.json")) {
  throw "ci-gate: missing composer.json"
}

# ext-sockets is optional for local quality gate; required only when using RoadRunner engine.
$modules = @(php -m)
$hasSockets = $modules | Where-Object { $_ -match "^sockets$" } | Measure-Object | Select-Object -ExpandProperty Count
if ($hasSockets -eq 0) {
  Write-Host "ci-gate: WARNING ext-sockets is missing; RoadRunner worker features are unavailable on this PHP."
}

Write-Host "ci-gate: composer install"
if ($env:RUNTIME_GATE_SKIP_COMPOSER -eq "1") {
  Write-Host "ci-gate: composer SKIP (RUNTIME_GATE_SKIP_COMPOSER=1)"
} else {
  $hasLock = Test-Path -Path "composer.lock"
  $needSyncLock = $false
  if ($hasLock) {
    try {
      $lockJson = Get-Content -Raw -Path "composer.lock" | ConvertFrom-Json
      $names = @()
      if ($lockJson.packages) { $names += @($lockJson.packages | ForEach-Object { $_.name }) }
      if ($lockJson."packages-dev") { $names += @($lockJson."packages-dev" | ForEach-Object { $_.name }) }
      if ($names -contains "rector/rector") {
        # rector/rector was removed from baseline; if lock still contains it, sync lock once.
        $needSyncLock = $true
      }
    } catch {
      $needSyncLock = $false
    }
  }
  if ($needSyncLock) {
    Write-Host "ci-gate: composer.lock contains rector/rector -> syncing lock (composer update)"
    if ($hasSockets -eq 0) {
      Invoke-Checked "ci-gate: composer update" { composer update --no-interaction --prefer-dist --no-progress --ignore-platform-req=ext-sockets }
    } else {
      Invoke-Checked "ci-gate: composer update" { composer update --no-interaction --prefer-dist --no-progress }
    }
    $hasLock = Test-Path -Path "composer.lock"
  }
  if (-not $hasLock) {
    Write-Host "ci-gate: composer.lock missing -> running composer update to generate lock"
    if ($hasSockets -eq 0) {
      Invoke-Checked "ci-gate: composer update" { composer update --no-interaction --prefer-dist --no-progress --ignore-platform-req=ext-sockets }
    } else {
      Invoke-Checked "ci-gate: composer update" { composer update --no-interaction --prefer-dist --no-progress }
    }
  } else {
    if ($hasSockets -eq 0) {
      Invoke-Checked "ci-gate: composer install" { composer install --no-interaction --prefer-dist --no-progress --ignore-platform-req=ext-sockets }
    } else {
      Invoke-Checked "ci-gate: composer install" { composer install --no-interaction --prefer-dist --no-progress }
    }
  }
}

if (-not (Test-Path -Path "vendor/autoload.php")) {
  throw "ci-gate: vendor/autoload.php not found (composer install failed)"
}

Invoke-Checked "ci-gate: verify bundle pack" { php tools/runtime/verify-bundle.php }
Invoke-Checked "ci-gate: verify endpoint security pack" { php tools/runtime/verify-endpoint-security.php }
Invoke-Checked "ci-gate: verify worker lifecycle pack" { php tools/runtime/verify-worker-lifecycle.php }
Invoke-Checked "ci-gate: verify worker reset pack" { php tools/runtime/verify-worker-reset.php }

Write-Host "ci-gate: metric contract"
$metricStrict = ($env:RUNTIME_METRIC_CONTRACT_STRICT -eq "1")
$metricBase = $env:RUNTIME_METRIC_BASE_URL
if ([string]::IsNullOrWhiteSpace($metricBase)) { $metricBase = "http://127.0.0.1:8080" }
if ($metricStrict) {
  Invoke-Checked "ci-gate: metric contract strict" { pwsh tools/runtime/check-metric-contract.ps1 -BaseUrl $metricBase -Strict }
} else {
  Invoke-Checked "ci-gate: metric contract" { pwsh tools/runtime/check-metric-contract.ps1 -BaseUrl $metricBase }
}

Invoke-Checked "ci-gate: phpunit" { php vendor/bin/phpunit -c phpunit.xml.dist }
Invoke-Checked "ci-gate: phpstan" { php vendor/bin/phpstan analyse -c phpstan.neon }

Write-Host "ci-gate: ok"
