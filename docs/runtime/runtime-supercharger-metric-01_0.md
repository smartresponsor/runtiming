Runtime Supercharger — metric v01_0

Goal
Provide a canonical, low-cost metrics surface for long-living Symfony workers:
- reset timings and counters
- worker recycle decisions by reason
- memory/uptime snapshots

Metric names (suggested)
- runtime_supercharger_reset_total{result="ok|fail"}
- runtime_supercharger_reset_duration_seconds_sum / _count
- runtime_supercharger_worker_decision_total{reason="<reason>", recycle="0|1"}
- runtime_supercharger_rss_memory_mb
- runtime_supercharger_uptime_sec
- runtime_supercharger_request_count

Exporter
- RuntimePrometheusFileExporter writes a single .prom file in Prometheus text format.

Integration idea
- Call exporter periodically (after request terminate) or on-demand via cron.
- Prefer writing into var/metric/ (singular paths by canon):
  - var/metric/runtime-supercharger.prom
