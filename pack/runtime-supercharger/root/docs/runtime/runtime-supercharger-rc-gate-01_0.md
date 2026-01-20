Runtime Supercharger — RC gate pack v01_0

Goal
Provide a single command that can be used as a release gate for long-running runtime:
- validate required runtime sketches are present (verifiers exist and pass),
- run a short bench+soak sample,
- compute p95 and fail-rate from status.csv,
- enforce thresholds (SLO gate) with a hard exit code.

Dependencies (expected in repo)
- sketch-33: tools/runtime/bench/run-bench.(ps1|sh)
- sketch-36: tools/runtime/bench/aggregate-status.py (optional but recommended)
- verify scripts from sketches 32..36 under tools/runtime/verify-*.php

Inputs (env)
- RUNTIME_GATE_BASE_URL (default http://127.0.0.1:8080)
- RUNTIME_GATE_ROUTE (default /status)
- RUNTIME_GATE_P95_MS_MAX (default 250)
- RUNTIME_GATE_FAIL_RATE_MAX (default 0.005)
- RUNTIME_GATE_MODE (default read)  # read|write just changes defaults if you prefer

Bench params (env forwarded to bench harness)
- RUNTIME_BENCH_* (see sketch-33)
  Defaults used by gate:
  - warmup 20, load 10s, soak 1m, concurrency 8, sample 2s

Outputs
- report/runtime/gate/<stamp>/gate.json
- report/runtime/gate/<stamp>/gate.log (PowerShell only)

Usage (Windows)
- tools/runtime/gate/run-rc-gate.ps1

Usage (Linux)
- tools/runtime/gate/run-rc-gate.sh

Interpretation
- PASS => exit 0
- FAIL => exit 1
