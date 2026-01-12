Runtime Supercharger — Production runbook v01_0

Purpose
Operate long-running PHP runtimes safely across engines (RR / FrankenPHP / Swoole) under Symfony 8 + PHP 8.5.

Core principles
- Warm kernel is a feature; state isolation is enforced by resetters and controlled recycling.
- Observe and gate. Do not "trust" long-running workers without telemetry.
- Prefer RR or FrankenPHP as primary engines; keep Swoole profile as advanced/optional.

Baseline configuration (recommended)
Endpoint security
- RUNTIME_ENDPOINT_SECURITY_ENABLED=1
- MODE=allowlist_or_token
- Allowlist: cluster CIDR only
- Token header: X-Runtime-Token
- Token: stored in secret manager; rotated

Worker lifecycle
- RUNTIME_WORKER_LIFECYCLE_ENABLED=1
- MAX_REQUEST=1000
- MAX_MEMORY_MB=512 (or lower depending on pod limits)
- MAX_UPTIME_SECOND=3600
- DRAIN_SECOND=10

Worker reset
- RUNTIME_WORKER_RESET_ENABLED=1
- KERNEL=1
- DOCTRINE=1

Engine selection
- RR: recommended for ops control; explicit worker supervision.
- FrankenPHP: excellent latency; verify worker mode config for your version.
- Swoole: only if you have a proven bridge and acceptance tests.

SLO gates (suggested)
Read-heavy services (Category/Order/Message read paths)
- p95 <= 250ms (read)
- error rate <= 0.5%
- worker recycle: no thrash (restarts stable, not cascading)
- memory: steady-state (no monotonic growth beyond +10% over 30 min soak)

Write-heavy paths
- p95 <= 700ms (write)
- error rate <= 0.5%

Go/No-Go checklist (RC -> prod)
- [ ] /status reachable from ops network, returns engine and build info
- [ ] /metrics reachable only from internal allowlist or with token
- [ ] bench+soak harness run completed (sketch-33) and reports stored
- [ ] recycle triggers validated (max_request OR max_memory OR max_uptime)
- [ ] reset triggers validated (kernel reset + doctrine reset)
- [ ] rollback plan prepared (disable worker mode / switch to FPM)
- [ ] alert rules in place (error rate, latency p95, worker restarts, OOM kills)

Rollout strategy
- Canary 48h (or 7d for higher risk) with strict SLO gate.
- Increase traffic gradually; monitor worker restart rate + memory trend.

Rollback
- Switch engine profile back to FPM (short-lived) if warm-state bug is suspected.
- Lower MAX_REQUEST to force frequent recycle as temporary mitigation.

Warm-state incident triage
Symptoms
- cross-request contamination (user A sees user B data)
- increasing memory / CPU over time
- random "already closed" DB connection errors
First actions
- enable aggressive recycle (MAX_REQUEST=50, MAX_UPTIME=600)
- ensure reset enabled (kernel+doctrine)
- capture status/metrics and last N logs around spikes
- bisect new caches/static variables and persistent singletons

Post-incident hardening
- add a resetter for any custom stateful service
- extend bench harness with a regression scenario for the incident
