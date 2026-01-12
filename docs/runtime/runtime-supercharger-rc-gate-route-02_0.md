Runtime Supercharger — RC gate (route-aware) v02_0

Goal
Turn the gate into a real service gate:
- probe real routes (read/write endpoints), compute p95 + fail-rate per route,
- fail the gate if any route violates thresholds,
- keep bench+soak harness as an optional extra check,
- produce deterministic artifacts (CSV + JSON) to attach to RC/GA.

Important note on PHP 9.5
- PHP 9.5 does not exist as a released branch today.
- This gate supports "PHP nightly / master (future 9.x)" posture via platform checks and by avoiding deprecated assumptions.
- Use nightly builds in CI if you want early warning; keep runtime logic strict and stateless.

Inputs (env)
- RUNTIME_GATE_BASE_URL (default http://127.0.0.1:8080)
- RUNTIME_GATE_ROUTE_LIST (default /status)
  Example:
    /status,/api/category/resolve?slug=test,/api/order/preview?id=1
- RUNTIME_GATE_MODE (read|write) (default read)
  - read defaults: p95<=250ms, fail<=0.5%
  - write defaults: p95<=700ms, fail<=0.5%
- RUNTIME_GATE_P95_MS_MAX (override)
- RUNTIME_GATE_FAIL_RATE_MAX (override)
- RUNTIME_GATE_PROBE_SECOND (default 15) per route
- RUNTIME_GATE_CONCURRENCY (default 8)

Headers
- RUNTIME_GATE_TOKEN (optional)
- RUNTIME_GATE_TOKEN_HEADER (default X-Runtime-Token)

Bench (optional)
- RUNTIME_GATE_RUN_BENCH (default 1)
  If enabled, will run sketch-33 bench harness (still focused on /status) and copy its artifacts.

Outputs
- report/runtime/gate/<stamp>/gate.json
- report/runtime/gate/<stamp>/route/<routeKey>/probe.csv
- report/runtime/gate/<stamp>/route/<routeKey>/eval.json
- report/runtime/gate/<stamp>/platform.json
- report/runtime/gate/<stamp>/gate.log

Usage
- Windows: tools/runtime/gate/run-rc-gate.ps1
- Linux:   tools/runtime/gate/run-rc-gate.sh

Exit code
- PASS => 0
- FAIL => 1
