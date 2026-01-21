#!/usr/bin/env bash

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
CADDYFILE="$ROOT/Caddyfile"

if [[ ! -f "$CADDYFILE" ]]; then
  cp "$ROOT/tools/frankenphp/template/Caddyfile" "$CADDYFILE"
  echo "created Caddyfile from template"
fi

if command -v frankenphp >/dev/null 2>&1; then
  exec frankenphp run --config "$CADDYFILE"
fi

if command -v caddy >/dev/null 2>&1; then
  exec caddy run --config "$CADDYFILE"
fi

if [[ -x "$ROOT/tools/frankenphp/frankenphp" ]]; then
  exec "$ROOT/tools/frankenphp/frankenphp" run --config "$CADDYFILE"
fi

if [[ -x "$ROOT/tools/frankenphp/caddy" ]]; then
  exec "$ROOT/tools/frankenphp/caddy" run --config "$CADDYFILE"
fi

echo "frankenphp/caddy binary not found (install FrankenPHP or Caddy with FrankenPHP module, or place binary in tools/frankenphp/)"
exit 2
