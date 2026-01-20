# Runtime Supercharger Observability (02_0)

This document describes the operator view for Runtime Supercharger: dashboards, alerts, and quick checks.

## Exported metrics

Runtime Supercharger exposes a minimal Prometheus surface intended to be stable across runtimes:

- `runtime_supercharger_reset_total{result="ok"|"fail"}`
- `runtime_supercharger_reset_duration_seconds_sum`
- `runtime_supercharger_reset_duration_seconds_count`
- `runtime_supercharger_reset_count`
- `runtime_supercharger_worker_decision_total{reason,recycle}`
- `runtime_supercharger_rss_memory_mb`
- `runtime_supercharger_uptime_sec`
- `runtime_supercharger_request_count`

Notes:
- `runtime_supercharger_request_count` is reported as a gauge and resets on worker restart.
- Reset duration is exported as a Prometheus summary-style `*_sum` / `*_count` pair.

## Grafana dashboard

Path: `ops/runtime/grafana/runtime-supercharger-dashboard.json`

Panels:
1) Reset rate (per result)
2) Reset average duration
3) RSS memory (MB)
4) Uptime (seconds)
5) Request count (gauge)
6) Worker decision rate (by reason / recycle)
7) Last reset count (gauge)
8) Reset failures (15m increase)

Datasource UID is set to `__PROM__` for import portability.

## Prometheus alerts

Path: `ops/runtime/prometheus/runtime-supercharger-alert-rule.yaml`

Alerts:
- `RuntimeSuperchargerResetFailure`: any reset failure in 10m
- `RuntimeSuperchargerResetStorm`: >10 resets in 5m for 10m
- `RuntimeSuperchargerRssHigh`: RSS > 1500MB for 15m
- `RuntimeSuperchargerUptimeFlapping`: avg uptime < 120s for 15m
- `RuntimeSuperchargerResetDurationSlow`: avg reset duration > 1s for 10m

## Quick operator checks

### Check metrics contract

Linux/macOS:
```bash
tools/runtime/check-metric-contract.sh http://127.0.0.1:8080
```

Windows PowerShell:
```powershell
tools/runtime/check-metric-contract.ps1 -BaseUrl http://127.0.0.1:8080
```

### Probe routes

Linux/macOS:
```bash
tools/runtime/gate/probe-route.sh http://127.0.0.1:8080 /status/worker
```

Windows PowerShell:
```powershell
tools/runtime/gate/probe-route.ps1 -BaseUrl http://127.0.0.1:8080 -Path /status/worker
```

## Common incident patterns

- Memory grows until recycle: RSS trends up, then uptime resets. Tighten recycle thresholds or add resetters.
- Reset storm: investigate resetter chain, request limit, and external deps (DB, cache).
- Reset slow: expensive resetters (container reset) or large service graph. Reduce scope or move to per-subsystem reset.
