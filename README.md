# Runtime Supercharger

Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

Symfony-oriented runtime utilities for long-running workers:
- recycle decisions (headers or decision objects)
- telemetry snapshots + Prometheus export
- `/status` and `/metrics` endpoints
- RoadRunner + FrankenPHP config examples

## Quick start (2 minutes)

1. Install dependencies: `composer install`
2. Enable runtime gate locally: `pwsh tools/runtime/ci-gate.ps1`
3. Verify status/metrics contracts:
   - `php tools/runtime/print-status.php`
   - `php tools/runtime/print-metrics.php`
4. If endpoint guard is enabled in your app, confirm `/status` behavior against the endpoint guide.

## Docs index

| Topic | Where to start |
| --- | --- |
| Runtime architecture/spec | `docs/runtime/academic-runtime-spec-01_0-supercharger.md` |
| Symfony lifecycle integration | `docs/runtime/runtime-supercharger-symfony-lifecycle-01_0.md` |
| Reset model and contracts | `docs/runtime/reset-architecture.md`, `docs/runtime/runtime-supercharger-reset-contract-01_0.md` |
| Telemetry and metrics | `docs/runtime/runtime-supercharger-telemetry-metrics-01_0.md`, `docs/runtime/runtime-supercharger-metrics-endpoint-symfony-01_0.md` |
| Endpoint status/guard | `docs/runtime/runtime-supercharger-status-endpoint-symfony-01_0.md`, `docs/runtime/security-endpoint-guard-test-matrix.md` |
| Production runbook | `docs/runtime/runtime-supercharger-prod-runbook-01_0.md` |
| Incident/debug checklist | `docs/runtime/runtime-supercharger-incident-playbook-01_0.md`, `docs/runtime/runtime-supercharger-debug-checklist-01_0.md` |
| CI evidence and artifacts | `docs/runtime/ci-evidence.md` |

## Architecture map

- **Telemetry flow**: worker/runtime emits snapshots → telemetry sink → `/metrics` surface and Prometheus scrape (`runtime-supercharger-telemetry-sink`, telemetry metrics, metrics endpoint docs).
- **Reset flow**: lifecycle/reset signals → reset registry contracts → concrete resetters (`reset-architecture`, reset contract, worker reset docs).
- **Lifecycle flow**: Symfony kernel + worker lifecycle hooks → runtime lifecycle policy/bridge (`symfony lifecycle`, `worker lifecycle`, lifecycle policy docs).
- **Endpoint security flow**: endpoint guard mode/token/proxy checks → `/status` and `/metrics` exposure policy (`endpoint security`, status endpoint doc, guard test matrix).

## Support matrix (PHP/Symfony)

| Axis | Tested in CI | Supported by package constraint |
| --- | --- | --- |
| PHP 8.4 | **Yes** (`runtime-gate-master` job uses 8.4) | **Yes** (`php: ^8.4`) |
| Symfony 6.4 | Planned (not explicitly matrix-tested) | Yes (`^6.4`) |
| Symfony 7.x | Planned (not explicitly matrix-tested) | Yes (`^7.0`) |
| Symfony 8.x | Planned (not explicitly matrix-tested) | Yes (`^8.0`) |

## Ops quick links

- Runbook: `docs/runtime/runtime-supercharger-prod-runbook-01_0.md`
- SLO gate/policy: `ops/runtime/runtime-supercharger-slo-gate.yaml`, `ops/runtime/runtime-supercharger-prod-policy.yaml`
- Dashboards/alerts:
  - `ops/runtime/grafana/runtime-supercharger-dashboard.json`
  - `ops/runtime/prometheus/runtime-supercharger-alert-rule.yaml`
  - `ops/runtime/prometheus/runtime-supercharger-scrape-job.yaml`

## CI gate

GitHub workflow:

```bash
bash tools/runtime/ci-gate.sh
```
