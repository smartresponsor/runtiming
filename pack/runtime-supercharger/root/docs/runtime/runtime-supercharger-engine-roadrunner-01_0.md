Runtime Supercharger — RoadRunner engine adapter v01_0

Goal
Run Symfony as a long-living warm-worker under RoadRunner, while preserving:
- stable lifecycle (boot once, handle many)
- telemetry per request
- safe recycle (controlled worker restart)
- correct multi-worker metrics via file snapshot sink

Key env / parameters
- APP_ENV, APP_DEBUG (standard Symfony)
- RUNTIME_ENGINE=rr (recommended)
- RUNTIME_WORKER_ID (optional; defaults to pid-<pid>)
- RUNTIME_TELEMETRY_DIR (optional; default var/runtime/telemetry)
- RUNTIME_RR_MAX_REQUEST (default 5000)
- RUNTIME_RR_MAX_UPTIME_SEC (default 1800)
- RUNTIME_RR_MAX_MEMORY_MB (default 512)

Recycle semantics
- Worker exits loop when any threshold is reached.
- RoadRunner will respawn the worker (supervision is rr responsibility).

Telemetry semantics
- Counters: request_total by status, recycle_total by reason
- Gauges: duration_count/sum/max, worker_start_time_second, worker_uptime_second, memory_high_water_byte

Run
1) Ensure rr binary is installed and available on PATH.
2) Ensure composer deps are installed.
3) Start:
   rr serve -c config/runtime/rr/rr.yaml
4) Verify:
   GET http://127.0.0.1:8080/status
   GET http://127.0.0.1:8080/metrics
5) Smoke:
   tools/runtime/rr-smoke.ps1   (Windows)
   tools/runtime/rr-smoke.sh    (Linux/macOS)

Notes
- /metrics and /status routes are provided by sketches 23/24 and must be imported in your app routes.
- Telemetry dir must be shared and writable by all workers.
