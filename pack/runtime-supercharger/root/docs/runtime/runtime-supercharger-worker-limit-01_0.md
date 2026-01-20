Runtime Supercharger — worker recycle guard v01_0

Goal
In long-living workers you must periodically recycle the worker to prevent memory leaks and state drift.
This guard produces a deterministic decision after each request.

Decision order (first match wins)
- maxMemoryMb     => reason "maxMemory"
- maxRequest      => reason "maxRequest"
- maxUptimeSec    => reason "maxUptime"
- softMemoryMb    => reason "softMemory"
- otherwise       => reason "ok"

Notes
- This sketch does not call exit(). It only returns a decision.
- The worker engine (FrankenPHP/RoadRunner/etc) is responsible for performing the recycle.
- Memory reading is best-effort:
  - Prefer Linux /proc/self/statm when available
  - Fallback to memory_get_usage(true) when /proc is not available

Suggested defaults (tune per service)
- maxRequest: 3000
- maxUptimeSec: 900  (15 min)
- softMemoryMb: 256
- maxMemoryMb: 384
