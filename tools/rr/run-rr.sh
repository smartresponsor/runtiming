!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
\
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
RR_BIN="$(command -v rr || true)"
if [[ -z "$RR_BIN" ]]; then
  if [[ -x "$ROOT/tools/rr/rr" ]]; then
    RR_BIN="$ROOT/tools/rr/rr"
  else
    echo "rr binary not found (install RoadRunner or place rr in tools/rr/)"
    exit 2
  fi
fi

if [[ ! -f "$ROOT/.rr.yaml" ]]; then
  cp "$ROOT/tools/rr/template/.rr.yaml" "$ROOT/.rr.yaml"
  echo "created .rr.yaml from template"
fi

exec "$RR_BIN" serve -c "$ROOT/.rr.yaml"
