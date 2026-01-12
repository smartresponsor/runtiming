#!/usr/bin/env bash
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
\
set -euo pipefail

need() {
  command -v "$1" >/dev/null 2>&1 || { echo "Missing command: $1" >&2; exit 2; }
}

need php
need frankenphp
need curl

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
WORKER="$ROOT/worker/frankenphp-worker.php"

echo "Starting FrankenPHP with worker: $WORKER"
frankenphp php-server --worker "$WORKER" -l 127.0.0.1:8080 >/tmp/frankenphp.out 2>/tmp/frankenphp.err &
PID=$!

sleep 2

BASE="http://127.0.0.1:8080"

code_status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/status" || true)
code_metrics=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/metrics" || true)

echo "/status=$code_status /metrics=$code_metrics"

if [[ "$code_status" -ne 200 ]]; then
  echo "/status failed ($code_status). Import runtime_status routes (sketch-24)." >&2
  kill -9 "$PID" || true
  exit 1
fi

if [[ "$code_metrics" -ne 200 ]]; then
  echo "/metrics failed ($code_metrics). Import runtime_telemetry routes (sketch-23)." >&2
  kill -9 "$PID" || true
  exit 1
fi

N=200
t0=$(date +%s%3N 2>/dev/null || python - <<'PY'
import time
print(int(time.time()*1000))
PY
)

for ((i=0;i<N;i++)); do
  code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/status" || true)
  if [[ "$code" -ne 200 ]]; then
    echo "bench failed at i=$i code=$code" >&2
    kill -9 "$PID" || true
    exit 1
  fi
done

t1=$(date +%s%3N 2>/dev/null || python - <<'PY'
import time
print(int(time.time()*1000))
PY
)

echo "bench ok: $N requests in $((t1-t0))ms"

echo "Stopping FrankenPHP"
kill -9 "$PID" || true
exit 0
