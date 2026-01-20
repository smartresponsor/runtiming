!/usr/bin/env bash
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail
BASE_URL="$1"
ROUTE="$2"
PROBE_SECOND="${3:-15}"
CONCURRENCY="${4:-8}"
OUT_CSV="$5"
TOKEN="${6:-}"
TOKEN_HEADER="${7:-X-Runtime-Token}"

echo "ts,http_code,ms,bytes" > "$OUT_CSV"
end_at=$(( $(date +%s) + PROBE_SECOND ))

hdr_args=()
if [[ -n "$TOKEN" ]]; then
  hdr_args=(-H "$TOKEN_HEADER: $TOKEN")
fi

one() {
  while [[ $(date +%s) -lt $end_at ]]; do
    ts="$(date +%s)"
    start_ms="$(python - <<'PY'\nimport time\nprint(int(time.time()*1000))\nPY)"
    code="$(curl -s -o /tmp/runtime-probe.tmp -w "%{http_code}" "${hdr_args[@]}" "${BASE_URL%/}$ROUTE" || echo 0)"
    end_ms="$(python - <<'PY'\nimport time\nprint(int(time.time()*1000))\nPY)"
    ms=$(( end_ms - start_ms ))
    bytes=0
    if [[ -f /tmp/runtime-probe.tmp ]]; then
      bytes=$(wc -c < /tmp/runtime-probe.tmp | tr -d ' ')
      rm -f /tmp/runtime-probe.tmp
    fi
    echo "$ts,$code,$ms,$bytes" >> "$OUT_CSV"
  done
}

pids=()
for i in $(seq 1 "$CONCURRENCY"); do
  one &
  pids+=($!)
done
for p in "${pids[@]}"; do wait "$p"; done
