#!/usr/bin/env bash

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
set -euo pipefail

# CI-ready static gate for Runtime Supercharger.
# NOTE: Full HTTP RC gate requires a host app running /metrics and /status.

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

echo "ci-gate: php"; php -v

if [[ ! -f "composer.json" ]]; then
  echo "ci-gate: missing composer.json"
  exit 2
fi

if [[ "${RUNTIME_GATE_SKIP_COMPOSER:-}" == "1" ]]; then
  echo "ci-gate: composer SKIP (RUNTIME_GATE_SKIP_COMPOSER=1)"
else
  if [[ ! -f "composer.lock" ]]; then
    echo "ci-gate: composer.lock missing -> running composer update to generate lock"
    composer update --no-interaction --prefer-dist --no-progress
  else
    echo "ci-gate: composer install"
    composer install --no-interaction --prefer-dist --no-progress
  fi
fi

echo "ci-gate: verify bundle pack"
php tools/runtime/verify-bundle.php

echo "ci-gate: verify endpoint security pack"
php tools/runtime/verify-endpoint-security.php

echo "ci-gate: verify worker lifecycle pack"
php tools/runtime/verify-worker-lifecycle.php

echo "ci-gate: verify worker reset pack"
php tools/runtime/verify-worker-reset.php

echo "ci-gate: metric contract"
bash tools/runtime/check-metric-contract.sh "${RUNTIME_METRIC_BASE_URL:-http://127.0.0.1:8080}"

echo "ci-gate: phpunit"
vendor/bin/phpunit -c phpunit.xml.dist

echo "ci-gate: phpstan"
vendor/bin/phpstan analyse -c phpstan.neon

echo "ci-gate: ok"
