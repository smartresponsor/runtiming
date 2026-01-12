Runtime Supercharger — telemetry & metrics v01_0

Goal
Warm workers need visibility:
- request throughput
- status distribution
- duration aggregates
- recycle reasons
- memory high-water and uptime

Telemetry design
- RuntimeTelemetry is a per-worker singleton service (warm state).
- It records request events and keeps counters/gauges.
- RuntimePrometheusExporter renders current state into Prometheus exposition format.

Metric set (stable)
Counters:
- runtime_supercharger_request_total{engine,status}
- runtime_supercharger_request_duration_count{engine}
- runtime_supercharger_request_duration_sum{engine}
- runtime_supercharger_recycle_total{engine,action,reason}

Gauges:
- runtime_supercharger_request_duration_max{engine}
- runtime_supercharger_memory_high_water_byte
- runtime_supercharger_worker_uptime_second
- runtime_supercharger_worker_start_time_second

Integration (wrapper loop)
- boot once per worker:
  $telemetry = new RuntimeTelemetry('runtime', static fn() => microtime(true), static fn() => (int) memory_get_usage(true));

- per request:
  $telemetry->beforeRequest($engine);
  ...
  $telemetry->afterRequest($engine, $statusCode, $recycle, $action, $reason);

Export
- $text = (new RuntimePrometheusExporter())->export($telemetry->snapshot());

Notes
- Labels are sanitized and escaped for Prometheus.
- Metrics are intentionally aggregate-only (no PII, no per-path cardinality).
