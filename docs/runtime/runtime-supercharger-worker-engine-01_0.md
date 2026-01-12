Runtime Supercharger — worker engine entrypoint v01_0

Goal
A warm worker must be able to:
- run forever under normal conditions
- recycle itself deterministically when requested by lifecycle policy
- work with multiple engines without mixing concerns

Contract: recycle headers
- X-Runtime-Supercharger-Recycle: "1"
- X-Runtime-Supercharger-Action: gracefulExit|hardExit
- X-Runtime-Supercharger-Reason: <string>

Directive mapping
- No recycle header -> continue
- Recycle + gracefulExit -> break loop and exit(0)
- Recycle + hardExit -> break loop and exit(1)

App handler contract
Set env var RUNTIME_APP_ENTRY to a PHP file that returns a callable:
- callable(array $context): RunnerResponse
Context always includes:
- engine: "roadrunner"|"frankenphp"
- nowFloat: microtime(true) at the moment the request is dispatched
Engine-specific context:
- roadrunner: psr7Request => Psr\Http\Message\ServerRequestInterface
- frankenphp: server|get|post|cookie|files|input

The handler is responsible for:
- boot (once per worker) if needed
- request handling, including invoking Symfony kernel/runner
- applying lifecycle policy and resetter chain between requests (sketch-18/19)
- returning RunnerResponse with recycle headers (sketch-19 injector)

Workers provided
- tool/runtime/worker-roadrunner.php:
  - uses Spiral\RoadRunner\Http\PSR7Worker loop
  - converts RunnerResponse to PSR-7 Response and responds
  - checks directive and exits if requested

- tool/runtime/worker-frankenphp.php:
  - uses frankenphp_handle_request() loop
  - writes status/headers/body via native functions
  - checks directive and exits if requested

Configuration examples
- config/runtime/roadrunner/rr.yaml
- config/runtime/frankenphp/Caddyfile

Notes
- Engines are the only place where process exit happens.
- Policy and reset stay in Runtime domain services, not in engine adapters.
