!/usr/bin/env bash
set -euo pipefail

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

BASE_URL="${1:-http://127.0.0.1:8080}"
METRICS_PATH="${METRICS_PATH:-/metrics}"
URL="${BASE_URL%/}${METRICS_PATH}"

if ! command -v curl >/dev/null 2>&1; then
  echo "curl is required" >&2
  exit 2
fi

body="$(curl -fsSL "$URL")"

required=(
  "runtime_supercharger_reset_total"
  "runtime_supercharger_reset_duration_seconds_sum"
  "runtime_supercharger_reset_duration_seconds_count"
  "runtime_supercharger_reset_count"
  "runtime_supercharger_worker_decision_total"
  "runtime_supercharger_rss_memory_mb"
  "runtime_supercharger_uptime_sec"
  "runtime_supercharger_request_count"
)

missing=0
for m in "${required[@]}"; do
  if ! grep -q "^${m}" <<<"$body"; then
    echo "missing metric: ${m}" >&2
    missing=1
  fi
done

# label contract check (best-effort)
if ! grep -q '^runtime_supercharger_reset_total{.*result=' <<<"$body"; then
  echo "missing label contract: runtime_supercharger_reset_total{result=...}" >&2
  missing=1
fi

if [ "$missing" -ne 0 ]; then
  echo "metric contract: FAIL" >&2
  exit 1
fi

echo "metric contract: OK"
