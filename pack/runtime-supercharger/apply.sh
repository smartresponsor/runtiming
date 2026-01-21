#!/usr/bin/env bash

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <target-root>" >&2
  exit 2
fi

TARGET_ROOT="$(cd "$1" && pwd)"
SELF_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PACK_ROOT="$SELF_DIR/root"
LIST_FILE="$SELF_DIR/file-list.txt"

if [[ ! -d "$PACK_ROOT" ]]; then
  echo "Pack root not found: $PACK_ROOT" >&2
  exit 1
fi
if [[ ! -f "$LIST_FILE" ]]; then
  echo "Pack file list not found: $LIST_FILE" >&2
  exit 1
fi

TS="$(date +%Y%m%d-%H%M%S)"
BACKUP_ROOT="$TARGET_ROOT/.pack-backup/$TS"
mkdir -p "$BACKUP_ROOT"

echo "[runtime-supercharger] Applying pack to: $TARGET_ROOT"
echo "[runtime-supercharger] Backup folder: $BACKUP_ROOT"

backup_existing() {
  local dst="$1"
  if [[ -e "$dst" ]]; then
    local rel="${dst#"$TARGET_ROOT"/}"
    local bdst="$BACKUP_ROOT/$rel"
    mkdir -p "$(dirname "$bdst")"
    cp -a "$dst" "$bdst"
  fi
}

copy_merge() {
  local src="$1"
  local dst="$2"
  mkdir -p "$(dirname "$dst")"
  if [[ -d "$src" ]]; then
    # Merge directory (do not delete existing files)
    cp -a "$src" "$dst"
  else
    cp -a "$src" "$dst"
  fi
}

while IFS= read -r rel; do
  [[ -z "$rel" ]] && continue
  [[ "$rel" == \#* ]] && continue

  src="$PACK_ROOT/$rel"
  dst="$TARGET_ROOT/$rel"

  if [[ ! -e "$src" ]]; then
    echo "[runtime-supercharger] Skip missing: $rel"
    continue
  fi

  backup_existing "$dst"
  copy_merge "$src" "$dst"
  echo "[runtime-supercharger] Copied: $rel"
done < "$LIST_FILE"

echo "[runtime-supercharger] Done. Smoke example:"
echo "  bash tools/runtime/ci-gate.sh http://localhost:8080"
