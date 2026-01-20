Runtime Supercharger — worker lifecycle v01_0

Goal
Make long-living worker processes predictable:
- prevent memory leaks from accumulating indefinitely,
- allow safe periodic "reboot" of container state,
- support drain behavior and readiness signaling.

What this sketch provides
- Policy: max_request, max_memory_mb, max_uptime_second.
- State: request count, start time, last recycle reason.
- Subscriber: integrates with Symfony kernel events (REQUEST/RESPONSE/TERMINATE).
- Terminator: exits after response to trigger supervisor restart.

How it works
1) REQUEST: increments worker request counter; optionally dispatches signals (pcntl only).
2) RESPONSE: if recycle is pending, adds:
   - X-Runtime-Recycle: 1
   - Connection: close
3) TERMINATE: evaluates policy again; if recycle is required, exits(0).

Config (bundle root)
runtime_supercharger:
  worker:
    lifecycle:
      enabled: true
      max_request: 1000
      max_memory_mb: 512
      max_uptime_second: 3600
      drain_second: 10

Environment overrides (fast switch)
- RUNTIME_WORKER_LIFECYCLE_ENABLED (default "1")
- RUNTIME_WORKER_MAX_REQUEST (default "1000")
- RUNTIME_WORKER_MAX_MEMORY_MB (default "512")
- RUNTIME_WORKER_MAX_UPTIME_SECOND (default "3600")
- RUNTIME_WORKER_DRAIN_SECOND (default "10")

Readiness/liveness guidance
- During drain: expose it through /status by reading RuntimeWorkerStateInterface (future integration),
  or gate by ingress readiness.
- Typical pattern: readiness fails when recycle is pending OR when drain window is active.

Operational checklist
- Under load, worker exits after ~max_request requests (observe restarts in supervisor logs).
- When memory grows above threshold, worker exits after finishing response.
- No request is terminated mid-flight (exit happens in TERMINATE).
