Runtime sketch-38

Purpose
- Upgrade RC gate pack to support real route gating (multiple routes, not only /status).
- Add platform check helpers for PHP nightly / future PHP 9.x readiness.
- Keep Windows-first (PowerShell) with bash equivalents.

What is new
- RUNTIME_GATE_ROUTE_LIST: comma-separated list of routes to probe under concurrency.
- Multi-route probe produces per-route CSV and a combined gate.json decision.
- Optional: still runs bench harness (sketch-33) as an extra safety check.

Formatting
- vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php
