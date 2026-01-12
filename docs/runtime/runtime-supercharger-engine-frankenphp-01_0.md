Runtime Supercharger — FrankenPHP engine adapter v01_0

Goal
Run Symfony as a long-living warm-worker under FrankenPHP, while preserving:
- stable lifecycle (boot once, handle many)
- telemetry per request
- safe recycle (controlled worker restart)
- correct multi-worker metrics via file snapshot sink

Key env / parameters
- APP_ENV, APP_DEBUG (standard Symfony)
- RUNTIME_ENGINE=frankenphp (recommended; set by worker if empty)
- RUNTIME_WORKER_ID (optional; defaults to pid-<pid>)
- RUNTIME_TELEMETRY_DIR (optional; default var/runtime/telemetry)
- RUNTIME_FRANKENPHP_MAX_REQUEST (default 0 = unlimited, use recycle thresholds)
- RUNTIME_FRANKENPHP_MAX_UPTIME_SEC (default 1800)
- RUNTIME_FRANKENPHP_MAX_MEMORY_MB (default 512)

Recycle semantics
- Worker exits loop when any threshold is reached.
- FrankenPHP will respawn the worker (supervision is FrankenPHP responsibility).

Telemetry semantics
- Counters: request_total by status, recycle_total by reason
- Gauges: duration_count/sum/max, worker_start_time_second, worker_uptime_second, memory_high_water_byte

Run (example)
1) Ensure frankenphp binary is installed and available on PATH.
2) Ensure composer deps for your app are installed.
3) Start in worker mode from project root:
   frankenphp php-server --worker ./worker/frankenphp-worker.php -l 127.0.0.1:8080
4) Import runtime routes from sketches 23/24.
5) Verify:
   GET http://127.0.0.1:8080/status
   GET http://127.0.0.1:8080/metrics

Smoke
- Windows: tool/runtime/frankenphp-smoke.ps1
- Linux/macOS: tool/runtime/frankenphp-smoke.sh

Notes
- Worker script follows the official FrankenPHP worker pattern (frankenphp_handle_request() loop).
- Telemetry dir must be shared and writable by all workers.
