#!/usr/bin/env bash

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail
stamp="$(date +%Y%m%dT%H%M%S)"
php_line=""
if command -v php >/dev/null 2>&1; then
  php_line="$(php -v 2>/dev/null | head -n 1)"
fi
cat <<JSON
{
  "stamp": "$stamp",
  "phpFound": $(if [[ -n "$php_line" ]]; then echo "true"; else echo "false"; fi),
  "phpVersion": "$(echo "$php_line" | sed 's/"/\\"/g')",
  "note": [
    "If you want PHP 9.x readiness, prefer running CI on nightly builds.",
    "PHP 9.5 is not a released branch; treat this as a future-readiness posture."
  ]
}
JSON
