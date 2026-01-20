# Runtime Supercharger

Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

Symfony-oriented runtime utilities for long-running workers:
- recycle decisions (headers or decision objects)
- telemetry snapshots + Prometheus export
- /status and /metrics endpoints
- RoadRunner + FrankenPHP config examples

## Local gate

```powershell
pwsh tools/runtime/ci-gate.ps1
```

## CI gate

The GitHub workflow uses:

```bash
bash tools/runtime/ci-gate.sh
```

## Minimal usage

Install deps:

```bash
composer install
```

Run a tool:

```bash
php tools/runtime/print-status.php
php tools/runtime/print-metrics.php
```
