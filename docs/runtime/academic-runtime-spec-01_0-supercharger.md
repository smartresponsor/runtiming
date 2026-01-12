Runtime Supercharger (alias: Octane) — academic spec v01_0
Status: sketch-1
Scope: Symfony 8 / PHP 8.5, default driver FrankenPHP worker-mode.

0. Purpose
Runtime Supercharger standardizes a long-living worker runtime for SmartResponsor Symfony components.
Goal: reduce per-request bootstrap overhead, improve p95 latency on read traffic, while staying state-safe.

1. Definitions
- Runtime: canonical domain holding runtime profiles, policies, and execution contracts.
- Supercharger: runtime mode that keeps Kernel and DI container hot between requests.
- Alias "Octane": human-facing shorthand for the Supercharger mode (inspired by Laravel Octane).
- Worker: long-living PHP process serving multiple requests.
- Request-cycle: input -> Kernel -> response, across worker boundaries.

2. Execution model (Supercharger mode)
2.1 Boot once
- Kernel boots once per worker.
- Container is built once and stays in memory.
- Warmup tasks may run after boot.

2.2 Serve many
- Each incoming request reuses the same Kernel/container.
- Framework invokes standard HTTP Kernel flow.

2.3 Reset boundary
- After each request, Runtime Supercharger MUST reset mutable services.
- Reset is idempotent and safe to call every request.

2.4 Controlled recycle
- Workers are recycled by policy (MAX_REQUESTS, memory thresholds, consecutive failures).
- Recycle MUST be observable (metrics/logs).

3. Default driver
3.1 FrankenPHP worker-mode is the default driver for RC1.
Reasons:
- official long-living mode
- simple ops footprint (Caddy/FrankenPHP only)
- native support in Symfony Runtime

3.2 Non-default drivers (out of RC1 scope)
- RoadRunner
- Swoole / OpenSwoole
They may be added later by introducing additional RuntimeProfile records, without changing core policies.

4. Warm state policy
4.1 What may be warmed
- pure services without per-request mutable state
- caches/registries that are reset-safe
- expensive configuration preloads
- doctrine metadata caches (NOT unit-of-work)

4.2 What MUST NOT be warmed/shared
- request-scoped objects
- security token state
- doctrine identity map / UoW
- mutable collections in singleton services
- static/global mutable variables

4.3 Warmup hooks
- Warmup runs only at worker boot.
- Warmup list is declared in config (not hardcoded in business domains).

5. Reset state contract
5.1 Mandatory reset triggers
- Kernel resetter MUST run after every request.
- Any service that can retain mutable state across requests MUST implement reset.

5.2 Service categories that are always reset
- Doctrine EntityManager/UnitOfWork
- in-memory registries
- HTTP clients with buffers
- custom caches that store non-immutable values
- long-lived iterators / streams

5.3 Reset guidelines
- reset must not allocate large new structures
- reset must not touch external I/O except freeing resources
- reset must be safe even if called twice

6. Recycle policy
6.1 Required controls
- MAX_REQUESTS (default starting point 500, tune per domain)
- memory ceiling per worker
- max consecutive failures

6.2 Required outcomes
- recycle on policy breach
- never silently continue with unsafe state
- record reason in logs/metrics

7. Observability requirements
7.1 Metrics
- runtime_worker_restart_total{reason}
- runtime_worker_request_total
- runtime_worker_mem_bytes
- runtime_recycle_duration_ms

7.2 Logs
- worker boot
- warmup completed
- reset errors
- recycle event with reason

8. Discovery loop (state-safety)
8.1 Static rules to detect risks
- static mutable properties in services
- global mutable variables
- caches storing request-scoped objects
- "singleton" services without reset but with mutable fields
- direct use of $_SESSION/$_SERVER mutation outside request scope

8.2 CI gate
- runtime-state-scan is mandatory before RC1.

9. Test matrix
- Unit: resetters, policies, profiles
- Integration: no stale state after N requests
- Perf: FPM baseline vs Supercharger
- Leak: memory plateau on 1k–5k requests
- Chaos: kill/recycle workers mid-flight, no data corruption

10. Adoption rules for business domains
- Business domains do not implement their own Supercharger logic.
- They consume Runtime profiles and comply with Reset/Warm rules.
- If a domain introduces new mutable long-lived services, it must add resetters per this spec.

11. Naming and packaging canon
- Canonical domain name: Runtime
- Track name: Runtime Supercharger
- Alias in docs only: Octane
- Artifacts use single hyphen, no plurals, EN-only comments in code.

End of spec.

