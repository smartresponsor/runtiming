Runtime Supercharger — canonical repo manifest

Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

This repo is a canonical merge of:
- runtime-supercharger-ready-repo (baseline bundle + minimal gating)
- runtime-sketch-37/38 (supercharger + worker engine + gate pack)

Canon decisions applied
- tool/ → tools/ (single entrypoint folder)
- config/runtime/roadrunner/ → config/runtime/rr/
- YAML/ENV/Caddyfile headers are comment-safe
- Domain folder removed (Domain is not a Symfony layer)
- InfraInterface removed (Infra is treated as moving/optional for this sketch)

Key areas
- src/Service + src/ServiceInterface: orchestration, runner, supercharger services
- src/Infra: sinks, filesystem, env config providers (optional)
- src/Http + src/HttpInterface: status/metrics endpoints
- src/Runtime: bundle + DI extension
- src/RuntimeInterface: runtime contracts used by bundle/infra
- tools/runtime: local smoke, fixtures, debug helpers
- tools/runtime/check-metric-contract.*: metric surface validation
- tools/k6: baseline/soak/spike bench scripts
- config/runtime/rr: RoadRunner configs
- config/runtime/frankenphp: FrankenPHP Caddyfile + env

Ops assets
- ops/runtime/grafana: dashboard JSON (Prometheus)
- ops/runtime/prometheus: ServiceMonitor + PrometheusRule

Quick checks
- CI static gate: tools/runtime/ci-gate.sh (used by .github/workflows/runtime-gate-master.yaml)
- Local: pwsh tools/runtime/ci-gate.ps1

Runtime endpoint guard P0 matrix
- Added branch-complete unit matrix for RuntimeEndpointGuard decision paths (mode, token parsing, proxy strict/trust, CIDR IPv4/IPv6, parse errors): `Test/Service/Runtime/RuntimeEndpointGuardTest.php`
- Added concise scenario-to-assertion mapping doc: `docs/runtime/security-endpoint-guard-test-matrix.md`
- Added focused smoke runner for the guard test group: `tools/smoke/runtime-endpoint-guard.sh`

Reset registry convergence (P0)
- Canonical reset spine is `App\ServiceInterface\Runtime\RuntimeResetRegistryInterface` backed by `App\Service\Runtime\RuntimeResetRegistry`.
- Legacy `App\Infra\Runtime\RuntimeResetterRegistry` is now an adapter over the canonical interface and marked deprecated (1.4.0 -> 1.6.0 window; warning only in dev/test).
- Deprecated `RuntimeSuperchargerService` now uses the canonical registry interface internally to avoid parallel reset paths.
- Architecture note added: `docs/runtime/reset-architecture.md`.
