Runtime Supercharger — RoadRunner profile v01_0

Goal
Run Symfony 8 on RoadRunner with long-running workers and keep state safe via:
- worker lifecycle policy (max requests/memory/uptime),
- worker reset registry (kernel reset + doctrine reset),
- endpoint security (metrics/status lock-down).

What you get
- .rr.yaml template with sane defaults (http, workers, reloads, logs).
- env templates for Runtime Supercharger (worker + endpoint security).
- Windows-friendly scripts to start/stop RR and run smoke checks.

Assumptions
- You have RoadRunner binary (rr) in PATH or in tools/rr/rr(.exe).
- You have a Symfony front controller public/index.php.

Recommended approach
- Use rr http server mode.
- Use workers command: php public/index.php (Symfony Runtime can adapt) OR use symfony runtime entry (if your app uses it).
  This sketch provides templates; you decide the exact command used in your app.

Minimal quick-start (dev)
1) Copy resource/template/rr/.rr.yaml to project root as .rr.yaml
2) Copy resource/template/rr/env.runtime-rr.env to .env.local (or compose env)
3) Run: tools/rr/run-rr.ps1 (Windows) or tools/rr/run-rr.sh (Linux)
4) Validate: curl http://127.0.0.1:8080/status (expect engine=rr)
5) Validate security: /metrics should require allowlist or token (per your env)

Production posture
- Put RR behind ingress (Envoy/Nginx/Traefik).
- Configure trusted proxies in Symfony (see sketch-29).
- Keep endpoint security enabled; expose /metrics only inside the cluster.

Common knobs
- RR worker pool: pool.num_workers, pool.max_jobs
- RR http: address, middleware, headers
- Runtime worker recycle: RUNTIME_WORKER_MAX_REQUEST / MAX_MEMORY_MB / MAX_UPTIME_SECOND
- Reset: RUNTIME_WORKER_RESET_* envs

Smoke checklist
- Under load, observe RR respawns workers (when Runtime lifecycle triggers exit(0) on terminate).
- Verify no state leak by running same request repeatedly and comparing memory/peak.
