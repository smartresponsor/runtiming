#!/usr/bin/env bash

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

# Collect a small evidence bundle for Runtime Supercharger.
# Output is stored under report/runtime/evidence/<UTC timestamp>/.
#
# Usage:
#   bash tools/runtime/evidence/collect-evidence.sh http://127.0.0.1:8080

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
cd "$ROOT"

TARGET_URL="${1:-http://127.0.0.1:8080}"
TS="$(date -u +%Y%m%dT%H%M%SZ)"
OUT="report/runtime/evidence/${TS}"

mkdir -p "$OUT"

_echo() { printf '%s\n' "$*"; }

_echo "evidence: target=${TARGET_URL}"
_echo "evidence: out=${OUT}"

# Basic health evidence.
if command -v curl >/dev/null 2>&1; then
  _echo "evidence: fetch /status"
  curl -fsS "${TARGET_URL}/status" > "${OUT}/status.json" || true

  _echo "evidence: fetch /metrics"
  curl -fsS "${TARGET_URL}/metrics" > "${OUT}/metrics.prom" || true
else
  _echo "evidence: curl not found (skipped HTTP fetch)"
fi

# Static gate (always available).
_echo "evidence: run static ci gate"
chmod +x tools/runtime/ci-gate.sh
bash tools/runtime/ci-gate.sh > "${OUT}/ci-gate.txt" 2>&1

# Optional RC HTTP gate (requires host app).
if [[ -x "tools/runtime/gate/run-rc-gate.sh" ]]; then
  _echo "evidence: run rc gate (if host is up)"
  RUNTIME_GATE_BASE_URL="${TARGET_URL}" bash tools/runtime/gate/run-rc-gate.sh > "${OUT}/rc-gate.txt" 2>&1 || true
fi

# Optional k6 (baseline only, short).
if command -v k6 >/dev/null 2>&1; then
  _echo "evidence: run k6 baseline"
  chmod +x tools/k6/run-k6-runtime.sh
  bash tools/k6/run-k6-runtime.sh baseline "${TARGET_URL}" > "${OUT}/k6-baseline.txt" 2>&1 || true
else
  _echo "evidence: k6 not found (skipped)"
fi

cat > "${OUT}/MANIFEST.txt" <<MAN
Runtime Supercharger evidence bundle

utc_timestamp=${TS}
target_url=${TARGET_URL}

files:
- status.json (optional)
- metrics.prom (optional)
- ci-gate.txt
- rc-gate.txt (optional)
- k6-baseline.txt (optional)
MAN

_echo "evidence: done"
