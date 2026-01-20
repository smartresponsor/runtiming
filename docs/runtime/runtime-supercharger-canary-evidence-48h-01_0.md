Runtime Supercharger — 48h canary evidence kit v01_0

Goal
Run a short stabilization window (48h) and produce a small evidence bundle per probe:
- /status snapshot
- /metrics snapshot
- CI/static gate output
- optional RC HTTP gate output
- optional k6 baseline output

Quick start (Linux)
1) Start your host runtime (RoadRunner/FrankenPHP/Swoole) so it exposes:
   - GET /status
   - GET /metrics
2) Collect evidence once:
   - bash tools/runtime/evidence/collect-evidence.sh http://127.0.0.1:8080
3) Evidence will be saved under:
   - report/runtime/evidence/<UTC timestamp>/

Quick start (Windows)
1) Start your host runtime (RoadRunner/FrankenPHP/Swoole).
2) Collect evidence once:
   - pwsh -NoProfile -ExecutionPolicy Bypass -File tools/runtime/evidence/collect-evidence.ps1 -TargetUrl http://127.0.0.1:8080
3) Evidence will be saved under:
   - report\runtime\evidence\<UTC timestamp>\

Recommended cadence (48h)
- Every 1 hour: collect evidence (create a timestamped folder)
- Every 6 hours: run RC gate explicitly
- Once per day: run k6 soak and keep the results separately

Pass criteria (baseline)
- static CI gate: PASS
- RC HTTP gate: PASS (p95 and fail-rate under thresholds)
- no metrics regression spikes
- no sustained memory leak (state scan should show stable counters)

Notes
- RC gate and k6 runs are optional in collect-evidence scripts.
- If your host requires a token header, configure it via RUNTIME_GATE_TOKEN / RUNTIME_GATE_TOKEN_HEADER.
