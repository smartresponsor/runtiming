Runtime Supercharger — FrankenPHP profile v01_0

Goal
Run Symfony 8 on FrankenPHP with long-running worker mode while keeping warm state safe:
- worker lifecycle policy (max requests/memory/uptime),
- worker reset registry (kernel reset + doctrine reset),
- endpoint security (metrics/status lock-down).

What you get
- Caddyfile template for dev (HTTP server).
- Caddyfile template for worker mode (commented example).
- env template for Runtime Supercharger (engine + endpoint + worker knobs).
- Windows-first scripts to run FrankenPHP (or Caddy if FrankenPHP is embedded).

Assumptions
- You have either:
  - frankenphp binary in PATH, or
  - caddy binary with the FrankenPHP module.
- Your Symfony app entry is public/index.php and assets are under public/.

Quick-start (dev)
1) Copy tools/frankenphp/template/Caddyfile to project root as "Caddyfile"
2) Copy tools/frankenphp/template/env.runtime-frankenphp.env to .env.local (or compose env)
3) Run tools/frankenphp/run-frankenphp.ps1 (Windows) or tools/frankenphp/run-frankenphp.sh (Linux)
4) Validate: curl http://127.0.0.1:8080/status (expect engine=frankenphp if you set env)

Worker mode
- Worker mode is optional but recommended for latency under load.
- Enable the worker stanza in Caddyfile and point it at public/index.php.
- In worker mode, the Symfony kernel stays warm; reset+recycle features are required.

Production posture
- Put Caddy behind ingress or run as ingress.
- Keep /metrics internal; use allowlist/token.
- Configure trusted proxies in Symfony (see prior runtime sketches).

Notes
- This sketch only provides templates and scripts to avoid conflicts with your existing runtime wiring.
- If you already have RuntimeEngineDetector wired (sketch-32), setting RUNTIME_ENGINE=frankenphp is enough.
