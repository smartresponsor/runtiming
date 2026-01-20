!/usr/bin/env bash
    Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

    URL="${1:-http://localhost:8080/health}"
    DURATION="${2:-30s}"
    CONCURRENCY="${3:-50}"

    if ! command -v wrk >/dev/null 2>&1; then
      echo "wrk is required. Install wrk and retry."
      exit 1
    fi

    echo "wrk $URL duration=$DURATION concurrency=$CONCURRENCY"
    wrk -t4 -c"$CONCURRENCY" -d"$DURATION" "$URL"
