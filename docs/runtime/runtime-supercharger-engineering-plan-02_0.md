# Runtime Supercharger Engineering Plan 02.0

## Snapshot

- Scope scanned: `src/`, `Test/`, `config/`, `resource/`, `tools/`, `.github/workflows/`, `ops/`, and top-level docs.
- Current package identity is `smartresponsor/runtime-supercharger` while runtime classes are consistently `RuntimeSupercharger*`.

## Findings by area

### 1) Architecture and boundaries

1. **Two reset paths with overlapping responsibilities**
   - Legacy-style reset flow uses `App\Infra\Runtime\RuntimeResetterRegistry` + `RuntimeSuperchargerService`.
   - Newer flow uses `App\Service\Runtime\RuntimeResetRegistry` + `RuntimeSupercharger` + lifecycle abstractions.
   - This creates boundary blur and dual mental models for one concern.

2. **Interface layer exists but is inconsistently consumed**
   - `ServiceInterface/Runtime` is rich and well-factored.
   - Multiple concrete classes still depend directly on concrete services (for example controllers depend on concrete aggregate/exporter/provider services).

3. **Bundle extension mixes config parsing and environment adaptation**
   - `RuntimeSuperchargerExtension` performs service loading, feature toggles, explicit parameter coercion, and fallback interpretation in one method.
   - This is workable now, but brittle for future variants (engine additions, new runtime modes).

4. **Config source split across `resource/config` and `config/*` trees**
   - Bundle runtime wiring lives in `resource/config/*.yaml`, while root `config/` tree includes demo/example/compat aliases.
   - Duplication is high and encourages drift.

### 2) Code quality

1. **Formatting and style inconsistency**
   - Many files include repeated extra blank lines around namespace/use blocks.
   - Copyright header style differs (`#` vs `//` in PHP files).

2. **Naming drift/compat clutter in config paths**
   - Both kebab-case and underscore variants exist for same logical config (`runtime-status.yaml` + `runtime_status.yaml`, etc.).
   - Duplicate service trees exist (`config/service/*` and `config/services/*`).

3. **Potential typo persistence in public config contract**
   - Lifecycle keys use singular forms like `max_uptime_second`, `drain_second`. If intentional, should be codified with migration aliases and docs; otherwise rename plan needed.

### 3) Tests

1. **Good baseline for extension, telemetry, prometheus, reset safety**
   - Existing tests cover several important internals.

2. **Critical gap: endpoint security logic has no direct unit coverage**
   - `RuntimeEndpointGuard` contains high-branch security logic (mode handling, proxy trust checks, CIDR, token parsing) without targeted tests.

3. **No matrix validation of engine adapters and config compatibility modes**
   - Adapters/config toggles are present but not exercised in dedicated scenario tests.

### 4) Reliability / predictability

1. **Error swallowing without metric/event correlation in some paths**
   - Fail-open/fail-soft behavior is implemented (good for uptime), but a few paths swallow exceptions silently without structured event emission.

2. **Potential hidden runtime behavior differences due to bool-ish strings**
   - Endpoint guard and extension parse bool-like strings manually in several places, increasing chance of divergence.

3. **CI gate depends on live dependency install each run**
   - Current workflows run install + checks but do not use dependency cache keys, impacting speed and increasing network sensitivity.

### 5) Documentation / operations

1. **Operational docs are rich but discoverability is weak**
   - Many runtime docs exist; top-level README does not include a clear docs index or architecture map.

2. **No explicit support policy table for PHP/Symfony matrix in README**
   - Composer constraints are broad; ops consumers would benefit from an explicit tested matrix.

3. **Runbook and SLO artifacts exist, but not linked from README quick start**
   - Production-readiness materials are present in `docs/runtime/` and `ops/runtime/` yet weakly surfaced.

### 6) Data/migrations

- This bundle does not own schema migrations directly; however, doctrine reset capabilities imply host-app DB state sensitivity.
- Missing: a documented deterministic fixture/seeding recipe for runtime integration checks in host apps.

### 7) CI/CD and infrastructure

1. **Workflow overlap**
   - `ci-gate.yml`, `ci-phpstan.yml`, and `runtime-gate-master.yaml` have overlapping scope and can diverge.

2. **No artifact publishing for test/coverage/diagnostic outputs**
   - Failing runs are harder to debug without archived logs, junit, and gate evidence.

3. **No explicit semver/release automation policy**
   - For a reusable bundle, release tagging and changelog automation are missing.

## Prioritized task list

### P0 (security/reliability first)

1. Add exhaustive unit tests for `RuntimeEndpointGuard` (modes, token, proxy strict, CIDR IPv4/IPv6, malformed inputs).
2. Unify reset architecture by selecting one public reset registry path and deprecating the other.
3. Introduce shared bool/config parsing helper used by extension + guard to avoid semantic drift.

### P1 (maintainability)

4. Split `RuntimeSuperchargerExtension::load()` into focused private methods (load core services, endpoint wiring, lifecycle wiring, reset wiring, parameter override passes).
5. Consolidate config naming strategy (canonical file names + backward-compatible aliases with documented deprecation window).
6. Add architectural map in README (core flows: telemetry, reset, lifecycle, endpoint security).

### P2 (operational excellence)

7. Add CI caching (`actions/cache` / composer cache) and publish junit/phpstan/gate artifacts.
8. Add support matrix table (PHP, Symfony) and tested combinations.
9. Add release checklist/changelog automation workflow.

## Suggested commit units

1. **Commit A: Security test hardening**
   - Add `Test/Service/Runtime/RuntimeEndpointGuardTest.php` with branch-complete matrix.

2. **Commit B: Reset architecture convergence**
   - Choose canonical reset registry interface and migrate legacy service path to it.
   - Mark legacy classes as deprecated (or remove if unused).

3. **Commit C: Extension refactor without behavior change**
   - Extract methods from `RuntimeSuperchargerExtension` and add focused tests for each feature toggle branch.

4. **Commit D: Config normalization**
   - Canonicalize file naming in `config/` + keep shims where needed.
   - Document compatibility/deprecation notes.

5. **Commit E: CI improvements**
   - Add dependency caching and artifacts upload in all gate workflows.

6. **Commit F: Documentation surfacing**
   - Expand README with docs index, architecture map, runbook links, and support matrix.

## Refactor blocks (can be scheduled independently)

- **Refactor Block 1:** Endpoint guard extraction
  - Extract `IpCidrMatcher`, `ProxyTrustPolicy`, and `TokenExtractor` collaborators.

- **Refactor Block 2:** Runtime extension composer
  - Introduce small loaders/registrars for endpoint, worker lifecycle, reset features.

- **Refactor Block 3:** Runtime reset domain consolidation
  - Converge `Infra` and `Service` reset paths into one domain model and one registry contract.

## Missing tests shortlist

1. `RuntimeEndpointGuard` unit matrix (P0).
2. `RuntimeWorkerLifecyclePolicy` edge cases (zero limits, threshold boundaries).
3. Controller tests for cache headers/content-type contracts.
4. Regression tests for config alias compatibility (`runtime-status` vs `runtime_status`).
5. Workflow smoke test script for `tools/runtime/ci-gate.sh` skip flags and failure modes.

## Missing infrastructure pieces

1. Centralized CI artifact retention (junit, phpstan report, runtime-gate evidence).
2. Release pipeline with version bump + changelog + tagged docs snapshot.
3. Optional container image publish path for runtime-tooling utilities.

## Execution proposal (first sprint)

- Sprint 1 (1 week): Commit A + Commit C + partial Commit F.
- Sprint 2 (1 week): Commit B + Commit D.
- Sprint 3 (3-5 days): Commit E + remaining docs and release automation.

