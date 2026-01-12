Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
$ErrorActionPreference = "Stop"

function Find-RR {
  $rr = Get-Command rr -ErrorAction SilentlyContinue
  if ($rr) { return $rr.Source }
  $local = Join-Path $PSScriptRoot "rr.exe"
  if (Test-Path $local) { return $local }
  $local2 = Join-Path $PSScriptRoot "rr"
  if (Test-Path $local2) { return $local2 }
  throw "rr binary not found (install RoadRunner or place rr.exe in tools/rr/)"
}

$rr = Find-RR
$root = Split-Path -Parent $PSScriptRoot | Split-Path -Parent

# copy template if missing
$rrYaml = Join-Path $root ".rr.yaml"
if (-not (Test-Path $rrYaml)) {
  Copy-Item (Join-Path $PSScriptRoot "template\.rr.yaml") $rrYaml -Force
  Write-Host "created .rr.yaml from template"
}

& $rr "serve" "-c" $rrYaml
