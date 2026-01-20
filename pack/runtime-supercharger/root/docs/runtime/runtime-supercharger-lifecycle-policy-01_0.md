Runtime Supercharger — lifecycle policy v01_0

Goal
A warm worker must be fast AND safe.
This sketch adds policy gates that make the warm worker self-healing:
- recycle on request count
- recycle on memory cap (high-water) or suspicious growth
- recycle on uptime/idle thresholds
- recycle on slow requests (duration gate)

Concept
- Policy decides; engine applies.
- Decision is expressed as response headers, so any engine adapter can react.

Headers contract
- X-Runtime-Supercharger-Recycle: "1" => request recycle
- X-Runtime-Supercharger-Action:
  - gracefulExit (preferred for most thresholds)
  - hardExit (emergency memory breach)
- X-Runtime-Supercharger-Reason: maxRequest|maxMemory|emergencyMemory|maxUptime|maxIdle|maxRequestDuration|memoryGrowth

Integration in wrapper-entrypoint loop
- boot policy once per worker
- per request:
  - policy->beforeRequest()
  - $res = $runner->handle($req)
  - emit $res
  - $runner->terminate($req, $res)
  - $dec = $policy->afterRequest()
  - $res2 = (new RuntimeLifecycleHeaderInjector())->apply($res, $dec)
  - emit headers (or keep a side-channel for engine action)
  - engine adapter reads headers and exits if needed

Notes
- Jitter is applied at boot so workers do not restart simultaneously.
- This sketch is intentionally conservative and explicit (no auto-discovery).
