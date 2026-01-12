# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$ErrorActionPreference = "Stop"

$stamp = Get-Date -Format "yyyyMMddTHHmmss"
$php = Get-Command php -ErrorAction SilentlyContinue
$out = @{
  stamp = $stamp
  phpFound = $false
  phpVersion = ""
  note = @(
    "If you want PHP 9.x readiness, prefer running CI on nightly builds.",
    "PHP 9.5 is not a released branch; treat this as a future-readiness posture."
  )
}

if ($php) {
  $out.phpFound = $true
  $v = & php -v 2>$null
  $out.phpVersion = ($v | Select-Object -First 1)
}

($out | ConvertTo-Json -Depth 8)
