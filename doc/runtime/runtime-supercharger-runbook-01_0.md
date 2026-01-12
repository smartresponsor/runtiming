Runtime Supercharger (alias Octane) — FrankenPHP worker runbook v01_0

0. Preconditions
- Symfony 8 app
- PHP 8.5 target
- Composer dep:
    composer require runtime/frankenphp-symfony

1. public/index.php changes
- Ensure APP_RUNTIME points to FrankenPHP Runtime.
- Ensure the front controller returns a callable for runtime.
See public/index.php.example.patch.

2. Worker config
- Use FrankenPHP worker-mode entrypoint:
    FRANKENPHP_CONFIG="worker ./public/index.php"
    APP_RUNTIME=Runtime\FrankenPhpSymfony\Runtime
- Control recycle:
    MAX_REQUESTS=500
    MAX_CONSECUTIVE_FAILURES=5

3. Start profile (docker)
- docker/runtime/compose.yaml provides a default dev/stage start.

4. Validate state-safety
- Run tool/runtime/smoke-runtime.sh (basic request loop + memory check).
- If memory grows linearly, reduce MAX_REQUESTS and add resetters (sketch-3).

5. Validate perf
- Run tool/runtime/bench-wrk.sh   (baseline)
- Run tool/runtime/bench-k6.js    (trend)

6. Adopt in Helm
- Copy ops/runtime/values-*.yaml overlays into your chart release.
- Ensure service port matches your ingress.

End.
