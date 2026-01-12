Runtime Supercharger — metrics endpoint (Symfony) v01_0

Goal
Expose correct multi-worker metrics from Symfony app without requiring a dedicated sidecar.

Design
- Controller reads aggregated snapshot from RuntimeTelemetryAggregate
- /metrics exports Prometheus exposition using RuntimePrometheusExporter
- /runtime/metrics/aggregate returns JSON for debugging

Security
- Production should protect /metrics with network policy / allow-list or auth.
- This sketch keeps it open by default (common in internal env).

Configuration
- env RUNTIME_TELEMETRY_DIR or parameter runtime_supercharger_telemetry_dir
- default dir: var/runtime/telemetry

Notes
- This is Runtime domain HTTP surface, not tied to any business domain.
