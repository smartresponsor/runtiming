!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
\
    set -euo pipefail

    ROOT="${1:-.}"
    FAIL_ON="${2:-error}"
    INCLUDE="${3:-src,config}"
    EXCLUDE="${4:-vendor,var,node_modules,public,tests,test}"

    if ! command -v php >/dev/null 2>&1; then
      echo "php is required on PATH."
      exit 2
    fi

    DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    php "$DIR/state-scan.php" --root="$ROOT" --failOn="$FAIL_ON" --include="$INCLUDE" --exclude="$EXCLUDE"
