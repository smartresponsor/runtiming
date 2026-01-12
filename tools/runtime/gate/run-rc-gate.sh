#!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

BASE_URL="${RUNTIME_GATE_BASE_URL:-http://127.0.0.1:8080}"
ROUTE_LIST="${RUNTIME_GATE_ROUTE_LIST:-/status}"
MODE="${RUNTIME_GATE_MODE:-read}"

if [[ -z "${RUNTIME_GATE_P95_MS_MAX:-}" ]]; then
  if [[ "$MODE" == "write" ]]; then export RUNTIME_GATE_P95_MS_MAX="700"; else export RUNTIME_GATE_P95_MS_MAX="250"; fi
fi
if [[ -z "${RUNTIME_GATE_FAIL_RATE_MAX:-}" ]]; then export RUNTIME_GATE_FAIL_RATE_MAX="0.005"; fi

PROBE_SECOND="${RUNTIME_GATE_PROBE_SECOND:-15}"
CONCURRENCY="${RUNTIME_GATE_CONCURRENCY:-8}"

TOKEN="${RUNTIME_GATE_TOKEN:-}"
TOKEN_HEADER="${RUNTIME_GATE_TOKEN_HEADER:-X-Runtime-Token}"

RUN_BENCH="${RUNTIME_GATE_RUN_BENCH:-1}"

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
STAMP="$(date +%Y%m%dT%H%M%S)"
OUT_DIR="$ROOT/report/runtime/gate/$STAMP"
ROUTE_DIR="$OUT_DIR/route"
mkdir -p "$ROUTE_DIR"

log() { echo "$1" | tee -a "$OUT_DIR/gate.log"; }

# platform snapshot
bash "$(dirname "${BASH_SOURCE[0]}")/check-platform.sh" > "$OUT_DIR/platform.json" || true

# verify dependency verifiers
verify_list=(
  "tool/runtime/verify-roadrunner-adapter.php"
  "tool/runtime/verify-bench-soak.php"
  "tool/runtime/verify-frankenphp-adapter.php"
  "tool/runtime/verify-swoole-adapter.php"
  "tool/runtime/verify-prod-runbook-pack.php"
)
for rel in "${verify_list[@]}"; do
  if [[ ! -f "$ROOT/$rel" ]]; then
    log "missing dependency verify: $rel"
    exit 1
  fi
  log "verify: $rel"
  php "$ROOT/$rel" >/dev/null
done

probe_sh="$(dirname "${BASH_SOURCE[0]}")/probe-route.sh"
eval_py="$(dirname "${BASH_SOURCE[0]}")/evaluate-multi-gate.py"

csvs=()
IFS=',' read -r -a routes <<< "$ROUTE_LIST"
for r in "${routes[@]}"; do
  r="$(echo "$r" | xargs)"
  [[ -z "$r" ]] && continue
  key="$(echo "$r" | sed -E 's/[^a-zA-Z0-9]+/-/g; s/^-+//; s/-+$//')"
  [[ -z "$key" ]] && key="route"
  dir="$ROUTE_DIR/$key"
  mkdir -p "$dir"
  csv="$dir/probe.csv"
  log "probe: $r"
  bash "$probe_sh" "$BASE_URL" "$r" "$PROBE_SECOND" "$CONCURRENCY" "$csv" "$TOKEN" "$TOKEN_HEADER"
  csvs+=("$csv")
done

log "gate: evaluate (multi-route)"
python "$eval_py" "${csvs[@]}" > "$OUT_DIR/gate.json"

# optional bench harness
if [[ "$RUN_BENCH" == "1" && -f "$ROOT/tools/runtime/bench/run-bench.sh" ]]; then
  log "bench: start (optional)"
  bash "$ROOT/tools/runtime/bench/run-bench.sh" >/dev/null || true
  bench_root="$ROOT/report/runtime/bench"
  latest="$(ls -1 "$bench_root" 2>/dev/null | sort -r | head -n 1 || true)"
  if [[ -n "$latest" ]]; then
    [[ -f "$bench_root/$latest/summary.json" ]] && cp "$bench_root/$latest/summary.json" "$OUT_DIR/bench-summary.json" || true
    [[ -f "$bench_root/$latest/status.csv" ]] && cp "$bench_root/$latest/status.csv" "$OUT_DIR/bench-status.csv" || true
    [[ -f "$bench_root/$latest/metrics.json" ]] && cp "$bench_root/$latest/metrics.json" "$OUT_DIR/bench-metrics.json" || true
  fi
fi

pass="$(python - <<PY
import json
p=json.load(open("$OUT_DIR/gate.json","r",encoding="utf-8"))
print("1" if p.get("pass") else "0")
PY
)"
if [[ "$pass" == "1" ]]; then
  log "PASS"
  exit 0
fi

log "FAIL"
exit 1
