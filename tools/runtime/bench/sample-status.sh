#!/usr/bin/env bash
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail
BASE_URL="$1"
OUT_DIR="$2"
SAMPLE_SECOND="$3"
TOKEN="${4:-}"
TOKEN_HEADER="${5:-X-Runtime-Token}"

OUT="$OUT_DIR/status.csv"
echo "ts,http_code,ms,bytes" > "$OUT"

hdr_args=()
if [[ -n "$TOKEN" ]]; then
  hdr_args=(-H "$TOKEN_HEADER: $TOKEN")
fi

while true; do
  ts="$(date +%s)"
  start_ms="$(python - <<'PY'\nimport time\nprint(int(time.time()*1000))\nPY)"
  code="$(curl -s -o /tmp/runtime-status.tmp -w "%{http_code}" "${hdr_args[@]}" "$BASE_URL/status" || echo 0)"
  end_ms="$(python - <<'PY'\nimport time\nprint(int(time.time()*1000))\nPY)"
  ms=$(( end_ms - start_ms ))
  bytes=0
  if [[ -f /tmp/runtime-status.tmp ]]; then
    bytes=$(wc -c < /tmp/runtime-status.tmp | tr -d ' ')
    rm -f /tmp/runtime-status.tmp
  fi
  echo "$ts,$code,$ms,$bytes" >> "$OUT"
  sleep "$SAMPLE_SECOND"
done
