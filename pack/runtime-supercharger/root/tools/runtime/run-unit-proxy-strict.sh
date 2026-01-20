!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
\
set -euo pipefail
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
php "$DIR/verify-proxy-strict.php"
ROOT="$(cd "$DIR/../.." && pwd)"
if [[ -f "$ROOT/vendor/autoload.php" ]]; then
  php "$DIR/guard-smoke.php"
else
  echo "ok (verify only; no vendor/autoload.php)"
fi
