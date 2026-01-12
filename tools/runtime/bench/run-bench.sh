#!/usr/bin/env bash
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

BASE_URL="${RUNTIME_BENCH_BASE_URL:-http://127.0.0.1:8080}"
ROUTE="${RUNTIME_BENCH_ROUTE:-/status}"
WARMUP_COUNT="${RUNTIME_BENCH_WARMUP_COUNT:-50}"
LOAD_SECOND="${RUNTIME_BENCH_LOAD_SECOND:-30}"
SOAK_MINUTE="${RUNTIME_BENCH_SOAK_MINUTE:-10}"
CONCURRENCY="${RUNTIME_BENCH_CONCURRENCY:-8}"
SAMPLE_SECOND="${RUNTIME_BENCH_SAMPLE_SECOND:-5}"
TOKEN="${RUNTIME_BENCH_TOKEN:-}"
TOKEN_HEADER="${RUNTIME_BENCH_TOKEN_HEADER:-X-Runtime-Token}"

STAMP="$(date +%Y%m%dT%H%M%S)"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
OUT_DIR="$ROOT/report/runtime/bench/$STAMP"
mkdir -p "$OUT_DIR"

hdr_args=()
if [[ -n "$TOKEN" ]]; then
  hdr_args=(-H "$TOKEN_HEADER: $TOKEN")
fi

# warmup
for i in $(seq 1 "$WARMUP_COUNT"); do
  curl -s -o /dev/null "${hdr_args[@]}" "$BASE_URL$ROUTE" || true
  sleep 0.02
done

# sampler
bash "$(dirname "${BASH_SOURCE[0]}")/sample-status.sh" "$BASE_URL" "$OUT_DIR" "$SAMPLE_SECOND" "$TOKEN" "$TOKEN_HEADER" &
SAMPLER_PID=$!

# load
end_at=$(( $(date +%s) + LOAD_SECOND ))
load_worker() {
  while [[ $(date +%s) -lt $end_at ]]; do
    curl -s -o /dev/null "${hdr_args[@]}" "$BASE_URL$ROUTE" || true
  done
}
pids=()
for i in $(seq 1 "$CONCURRENCY"); do
  load_worker &
  pids+=($!)
done
for p in "${pids[@]}"; do wait "$p"; done

# soak
sleep $(( SOAK_MINUTE * 60 ))

kill "$SAMPLER_PID" || true

# metrics
if curl -s -o "$OUT_DIR/metrics.prom" "${hdr_args[@]}" "$BASE_URL/metrics" ; then
  python "$(dirname "${BASH_SOURCE[0]}")/parse-prom-metrics.py" < "$OUT_DIR/metrics.prom" > "$OUT_DIR/metrics.json" || true
else
  rm -f "$OUT_DIR/metrics.prom" || true
fi

cat > "$OUT_DIR/summary.json" <<JSON
{
  "stamp": "$STAMP",
  "baseUrl": "$BASE_URL",
  "route": "$ROUTE",
  "warmupCount": $WARMUP_COUNT,
  "loadSecond": $LOAD_SECOND,
  "soakMinute": $SOAK_MINUTE,
  "concurrency": $CONCURRENCY,
  "sampleSecond": $SAMPLE_SECOND
}
JSON

echo "ok: $OUT_DIR"
