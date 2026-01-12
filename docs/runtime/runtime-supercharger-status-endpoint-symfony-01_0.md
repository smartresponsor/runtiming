Runtime Supercharger — status endpoint (Symfony) v01_0

Goal
Provide stable /status and host status JSON endpoints aligned with SmartResponsor ops norms.

Endpoints
1) GET /status
- per-worker view (the request hits one worker)
- includes uptime, start time, memory, PHP version, engine, workerId

2) GET /runtime/status/host
- host view derived from telemetry directory snapshots (sketch-22)
- includes worker snapshot count and last snapshot update time

Security
- Keep endpoints internal (ingress allow-list) or add auth later.
- This sketch keeps endpoints open by default.

Notes
- Host status does not parse snapshot JSON; it uses filesystem mtime for robustness.
- If telemetry dir is not mounted/writable, host status reports it as not ready.
