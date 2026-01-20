# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

param(
  [Parameter(Mandatory=$false)]
  [string]$BaseUrl = "http://127.0.0.1:8080",

  [Parameter(Mandatory=$false)]
  [ValidateSet("baseline","soak","spike")]
  [string]$Mode = "baseline"
)

if (-not (Get-Command k6 -ErrorAction SilentlyContinue)) {
  Write-Error "k6 is required (https://k6.io/)"
  exit 2
}

$PSScriptRootPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $PSScriptRootPath

$env:BASE_URL = $BaseUrl

switch ($Mode) {
  "baseline" { k6 run runtime-baseline.js }
  "soak" { k6 run runtime-soak.js }
  "spike" { k6 run runtime-spike.js }
}
