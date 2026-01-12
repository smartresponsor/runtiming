#!/usr/bin/env bash
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

# CI perf gate for Runtime Supercharger.
# Expects baseline p95 and current p95 in ms.
#
# Usage:
#   BASELINE_P95=220 CURRENT_P95=230 ./ci-perf-gate.sh
# Exit non-zero if regression > 5%.

BASELINE_P95="${BASELINE_P95:-}"
CURRENT_P95="${CURRENT_P95:-}"
ALLOW_RATIO="${ALLOW_RATIO:-1.05}"

if [[ -z "$BASELINE_P95" || -z "$CURRENT_P95" ]]; then
  echo "BASELINE_P95 and CURRENT_P95 are required."
  exit 2
fi

ratio=$(awk "BEGIN{print $CURRENT_P95 / $BASELINE_P95}")
allow=$ALLOW_RATIO

echo "baseline_p95=${BASELINE_P95}ms current_p95=${CURRENT_P95}ms ratio=${ratio} allow=${allow}"

ok=$(awk "BEGIN{print ($ratio <= $allow) ? 1 : 0}")
if [[ "$ok" -ne 1 ]]; then
  echo "Perf regression detected."
  exit 1
fi

echo "Perf gate passed."
