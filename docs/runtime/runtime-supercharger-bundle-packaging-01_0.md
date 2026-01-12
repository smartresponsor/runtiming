Runtime Supercharger — bundle packaging (Symfony) v01_0

Goal
Turn the Runtime Supercharger feature set into a first-class Symfony capability:
- one bundle
- one config root key
- stable parameter/env names
- optional endpoint wiring

Public surface (frozen)
- config root: runtime_supercharger
- telemetry dir env: RUNTIME_TELEMETRY_DIR
- worker id env: RUNTIME_WORKER_ID
- engine env: RUNTIME_ENGINE
- container parameter: runtime_supercharger_telemetry_dir
- routes:
  - /metrics
  - /runtime/metrics/aggregate
  - /status
  - /runtime/status/host

Bundle behavior
- always loads core services (aggregate/exporter/status provider/dir inspector)
- loads HTTP controller services only if enabled in config:
  runtime_supercharger:
    endpoint:
      metrics: true|false
      status: true|false

Route import is explicit (recommended)
- import resource/config/route-metrics.yaml if metrics endpoint is enabled
- import resource/config/route-status.yaml if status endpoint is enabled

Reason for explicit route import
- keeps endpoint exposure intentional in prod
- avoids silent changes in routing surface

End-product trajectory
- sketch-25 completes “packaging” step required for commercial readiness.
- next: real engine adapters + smoke under RoadRunner and FrankenPHP.
