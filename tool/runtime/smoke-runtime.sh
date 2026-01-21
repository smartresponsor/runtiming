#!/usr/bin/env bash

    Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

    URL="${1:-http://localhost:8080/status}"
    N="${2:-200}"

    echo "Smoke start: $URL x $N"

    for i in $(seq 1 "$N"); do
      curl -fsS "$URL" > /dev/null
      if (( i % 50 == 0 )); then
        echo "  ok $i"
      fi
    done

    echo "Smoke done. Check container memory plateau manually (docker stats)."
