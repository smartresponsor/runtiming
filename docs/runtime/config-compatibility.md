# Runtime Config Compatibility Matrix

Canonical runtime config layout is now:

- single-hyphen file names (`runtime-status.yaml`)
- singular directories (`config/service`, `config/route`, `config/package`)

Backward-compatible aliases are still shipped as thin `imports` shims.

## Deprecation window

- Deprecation introduced: **2025-01-01**
- Alias removal target: **2026-06-30**

After the removal date, aliases listed below will be deleted and only canonical paths will remain.

## Mapping table

| Old path | New canonical path | Support until |
|---|---|---|
| `config/service/runtime_status.yaml` | `config/service/runtime-status.yaml` | 2026-06-30 |
| `config/service/runtime_telemetry.yaml` | `config/service/runtime-telemetry.yaml` | 2026-06-30 |
| `config/route/runtime_status.yaml` | `config/route/runtime-status.yaml` | 2026-06-30 |
| `config/route/runtime_telemetry.yaml` | `config/route/runtime-telemetry.yaml` | 2026-06-30 |
| `config/package/runtime_status.yaml` | `config/package/runtime-status.yaml` | 2026-06-30 |
| `config/package/runtime_telemetry.yaml` | `config/package/runtime-telemetry.yaml` | 2026-06-30 |
| `config/services/runtime_supercharger.yaml` | `config/service/runtime-supercharger.yaml` | 2026-06-30 |
| `config/services/runtime_supercharger_config.yaml` | `config/service/runtime-supercharger-config.yaml` | 2026-06-30 |
| `config/services/runtime_supercharger_engine_adapter.yaml` | `config/service/runtime-supercharger-engine-adapter.yaml` | 2026-06-30 |
| `config/services/runtime_supercharger_metric.yaml` | `config/service/runtime-supercharger-metric.yaml` | 2026-06-30 |
| `config/services/runtime_supercharger_symfony_lifecycle.yaml` | `config/service/runtime-supercharger-symfony-lifecycle.yaml` | 2026-06-30 |
| `config/services/runtime_supercharger_wrapper_entrypoint.yaml` | `config/service/runtime-supercharger-wrapper-entrypoint.yaml` | 2026-06-30 |

## Notes

- Compatibility shims only redirect via `imports`; they do not change runtime behavior.
- `max_uptime_second` and `drain_second` are treated as public config contract keys in this slice and are not renamed here.
