# Runtime reset architecture

## Canonical public contract

`App\ServiceInterface\Runtime\RuntimeResetRegistryInterface` is the single public reset registry contract.

- `resetAll(): RuntimeResetReport` is the only reset-registry API for runtime flows.
- Entry points (HTTP subscribers, middleware, lifecycle orchestrators, supercharger services) must depend on this interface instead of concrete classes.
- The canonical implementation is `App\Service\Runtime\RuntimeResetRegistry`.

## Reset flow

1. Public entry point calls `RuntimeResetRegistryInterface::resetAll()`.
2. `RuntimeResetRegistry` iterates over `runtime_supercharger_resetter` tagged resetters.
3. Each `RuntimeResetterInterface` performs an isolated reset operation and can append errors into `RuntimeResetReport`.
4. The caller decides how to expose/log report errors while keeping request flow resilient.

## Legacy migration and deprecation window

Legacy `App\Infra\Runtime\RuntimeResetterRegistry` is preserved only as an adapter to the canonical interface.

- Status: deprecated since `1.4.0`.
- Removal target: `1.6.0`.
- Runtime warning policy: deprecation warning is emitted only in `dev` and `test` environments.

`App\Service\Runtime\RuntimeSuperchargerService` and its interface are also deprecated and internally use `RuntimeResetRegistryInterface` to avoid a second reset spine.

## DI wiring policy

- Keep one registry spine in DI: `RuntimeResetRegistryInterface -> RuntimeResetRegistry`.
- Keep legacy adapter services only as aliases/adapters for backward compatibility.
- Do not introduce new parallel registry services with overlapping semantics.
