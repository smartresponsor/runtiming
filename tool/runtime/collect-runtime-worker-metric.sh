#!/usr/bin/env bash

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

# Textfile collector for docker-based FrankenPHP workers.
# Writes:
#  - runtime_worker_mem_bytes
#  - runtime_worker_restart_total{reason="unknown"} (from container restart count)
#
# Usage:
#   ./collect-runtime-worker-metric.sh runtime-supercharger /var/lib/node_exporter/textfile_collector runtime.prom

CONTAINER="${1:-runtime-supercharger}"
OUT_DIR="${2:-./}"
OUT_FILE="${3:-runtime.prom}"

mkdir -p "$OUT_DIR"

# memory in bytes
MEM=$(docker stats --no-stream --format "{{.MemUsage}}" "$CONTAINER" | awk -F'/' '{print $1}' | tr -d ' ')

# Convert MEM like "123.4MiB" "1.2GiB" to bytes
bytes() {
  local v="$1"
  if [[ "$v" =~ ^([0-9.]+)KiB$ ]]; then awk "BEGIN{print ${BASH_REMATCH[1]}*1024}"
  elif [[ "$v" =~ ^([0-9.]+)MiB$ ]]; then awk "BEGIN{print ${BASH_REMATCH[1]}*1024*1024}"
  elif [[ "$v" =~ ^([0-9.]+)GiB$ ]]; then awk "BEGIN{print ${BASH_REMATCH[1]}*1024*1024*1024}"
  elif [[ "$v" =~ ^([0-9.]+)B$ ]]; then echo "${BASH_REMATCH[1]}"
  else echo "0"
  fi
}

MEM_BYTES=$(bytes "$MEM")

# restart count
RESTARTS=$(docker inspect "$CONTAINER" --format='{{.RestartCount}}')

cat > "$OUT_DIR/$OUT_FILE" <<EOF
# HELP runtime_worker_mem_bytes Resident memory of runtime workers (docker aggregate).
# TYPE runtime_worker_mem_bytes gauge
runtime_worker_mem_bytes $MEM_BYTES
# HELP runtime_worker_restart_total Total restarts of runtime workers (docker-based).
# TYPE runtime_worker_restart_total counter
runtime_worker_restart_total{reason="unknown"} $RESTARTS
EOF

echo "Wrote $OUT_DIR/$OUT_FILE (mem_bytes=$MEM_BYTES restarts=$RESTARTS)"
