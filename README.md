Runtime sketch-38

Purpose
- Upgrade RC gate pack to support real route gating (multiple routes, not only /status).
- Add platform check helpers for PHP nightly / future PHP 9.x readiness.
- Keep Windows-first (PowerShell) with bash equivalents.

Scope
- Responsibility: define Runtime Supercharger (Octane) worker-mode policies for Symfony 8 / PHP 8.5, focused on long-living worker safety, reset, recycle, and observability.
- Exclusions: RoadRunner and Swoole/OpenSwoole are out of RC1 scope.
- Target usage: FrankenPHP worker-mode runtime profile for RC1 adoption.

What is new
- RUNTIME_GATE_ROUTE_LIST: comma-separated list of routes to probe under concurrency.
- Multi-route probe produces per-route CSV and a combined gate.json decision.
- Optional: still runs bench harness (sketch-33) as an extra safety check.
