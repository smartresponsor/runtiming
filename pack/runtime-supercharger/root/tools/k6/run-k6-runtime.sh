#!/usr/bin/env bash

set -euo pipefail

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

BASE_URL="${1:-http://127.0.0.1:8080}"
MODE="${2:-baseline}"

if ! command -v k6 >/dev/null 2>&1; then
  echo "k6 is required (https://k6.io/)" >&2
  exit 2
fi

cd "$(dirname "$0")"

export BASE_URL

case "$MODE" in
  baseline)
    k6 run runtime-baseline.js
    ;;
  soak)
    k6 run runtime-soak.js
    ;;
  spike)
    k6 run runtime-spike.js
    ;;
  *)
    echo "Unknown mode: $MODE (baseline|soak|spike)" >&2
    exit 2
    ;;
 esac
