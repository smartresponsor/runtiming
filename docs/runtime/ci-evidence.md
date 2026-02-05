# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

# CI evidence and artifacts

## Workflows

- `build-test-gate` (`.github/workflows/ci-gate.yml`)
- `ci-phpstan` (`.github/workflows/ci-phpstan.yml`)
- `runtime-gate-master` (`.github/workflows/runtime-gate-master.yaml`)

Workflow names are preserved to avoid breaking existing branch protections and aliases.

## Cache strategy

A shared composite action (`.github/actions/php-runtime-setup/action.yml`) is used by all runtime CI workflows.

It configures:

- Composer download cache: `~/.composer/cache`
- Vendor cache: `vendor/`

Both caches are keyed by OS + PHP version + `composer.lock` hash to keep repeated runs fast and deterministic.

## Stable evidence paths

All CI jobs write reports into stable paths under `report/runtime/ci`.

- PHPUnit JUnit XML: `report/runtime/ci/junit.xml`
- PHPStan text report: `report/runtime/ci/phpstan.txt`
- RC gate outputs: `report/runtime/ci/gate/`
- Evidence bundle root: `report/runtime/ci/evidence/`

These paths are used directly by `actions/upload-artifact`, so artifacts are downloadable even when job steps fail (`if: always()`).

## Artifact names in GitHub Actions

- `build-test-gate-evidence`
- `ci-phpstan-evidence`
- `runtime-gate-master-evidence`

Open **Actions → workflow run → Artifacts** to download evidence bundles.
