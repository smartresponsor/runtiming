Runtime Supercharger — worker reset v01_0

Goal
Keep warm state safe in long-running PHP processes by resetting known stateful services after each request.

What is reset here
- Reset does not reboot the kernel, it clears/rewinds stateful services:
  - Symfony services resetter (kernel.reset tagged services)
  - Doctrine managers and connections (best-effort)
- This reduces memory growth and avoids cross-request contamination.

Config (bundle root)
runtime_supercharger:
  worker:
    reset:
      enabled: true
      kernel: true
      doctrine: true

Env overrides
- RUNTIME_WORKER_RESET_ENABLED (default "1")
- RUNTIME_WORKER_RESET_KERNEL (default "1")
- RUNTIME_WORKER_RESET_DOCTRINE (default "1")

Operational checklist
- Under long-running engine, repeated requests should not leak:
  - Doctrine identity map should not persist between requests
  - Kernel.reset services should be reset after each request
- If you use worker lifecycle recycle, reset is still useful (for the time window between recycles).

Notes
- Doctrine reset is best-effort and defensive: if doctrine is not installed, it is a no-op.
- Kernel resetter uses the Symfony ServicesResetter service (services_resetter) if present.
