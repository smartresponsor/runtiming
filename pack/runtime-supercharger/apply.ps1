# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

[CmdletBinding()]
param(
  [Parameter(Mandatory=$true)]
  [string]$TargetRoot
)

$ErrorActionPreference = 'Stop'

function Ensure-Directory([string]$Path) {
  if (-not (Test-Path -LiteralPath $Path)) {
    New-Item -ItemType Directory -Path $Path -Force | Out-Null
  }
}

function Backup-Existing([string]$TargetPath, [string]$BackupRoot, [string]$TargetRootCanonical) {
  if (-not (Test-Path -LiteralPath $TargetPath)) {
    return
  }

  $rel = $TargetPath.Substring($TargetRootCanonical.Length).TrimStart('\','/')
  $backupPath = Join-Path $BackupRoot $rel
  Ensure-Directory (Split-Path -Parent $backupPath)
  Copy-Item -LiteralPath $TargetPath -Destination $backupPath -Recurse -Force
}

function Copy-Merge([string]$SourcePath, [string]$TargetPath) {
  Ensure-Directory (Split-Path -Parent $TargetPath)
  if (Test-Path -LiteralPath $SourcePath -PathType Container) {
    # Merge folder into target
    Copy-Item -LiteralPath $SourcePath -Destination $TargetPath -Recurse -Force
    return
  }

  Copy-Item -LiteralPath $SourcePath -Destination $TargetPath -Force
}

$packRoot = Join-Path $PSScriptRoot 'root'
$fileList = Join-Path $PSScriptRoot 'file-list.txt'

if (-not (Test-Path -LiteralPath $packRoot)) {
  throw "Pack root not found: $packRoot"
}
if (-not (Test-Path -LiteralPath $fileList)) {
  throw "Pack file list not found: $fileList"
}

$TargetRoot = (Resolve-Path -LiteralPath $TargetRoot).Path

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$backupRoot = Join-Path $TargetRoot ".pack-backup\$timestamp"
Ensure-Directory $backupRoot

Write-Host "[runtime-supercharger] Applying pack to: $TargetRoot"
Write-Host "[runtime-supercharger] Backup folder: $backupRoot"

$entries = Get-Content -LiteralPath $fileList | Where-Object { $_ -and -not $_.StartsWith('#') }
foreach ($rel in $entries) {
  $src = Join-Path $packRoot $rel
  if (-not (Test-Path -LiteralPath $src)) {
    Write-Host "[runtime-supercharger] Skip missing: $rel"
    continue
  }

  $dst = Join-Path $TargetRoot $rel
  Backup-Existing -TargetPath $dst -BackupRoot $backupRoot -TargetRootCanonical $TargetRoot
  Copy-Merge -SourcePath $src -TargetPath $dst
  Write-Host "[runtime-supercharger] Copied: $rel"
}

Write-Host "[runtime-supercharger] Done. Smoke example:" 
Write-Host "  pwsh -NoProfile -File tools/runtime/ci-gate.ps1 -BaseUrl http://localhost:8080"
