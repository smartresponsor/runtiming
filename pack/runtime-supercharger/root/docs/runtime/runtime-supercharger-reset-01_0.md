Runtime Supercharger reset v01_0

Goal
In long-living workers you must actively reset request-scoped state. Otherwise you risk:
- cross-request data leaks
- memory growth
- stale auth/tenant context
- unexpected cache/registry contamination

Symfony facts (what exists out of the box)
- Symfony supports resetting services for long-running processes via a reset mechanism:
  services can be tagged and then reset between "iterations" (requests/messages).
- Messenger already resets tagged services between messages.
- For HTTP workers (FrankenPHP / RoadRunner), you must ensure "reset" happens between requests.

This sketch contribution
- RuntimeResetRegistry: ordered list of resetters, deterministic, safe timing report.
- RuntimeResetMiddleware: a simple wrapper that runs resetBefore/resetAfter around a callable.
- RuntimeResetReport: summary (count, time, per resetter).

Integration patterns (recommended)
1) Worker loop
   while (true) {
     $middleware->call(fn() => handleRequest());
   }

2) Symfony HttpKernel event
   - call resetAfter on kernel.terminate (after response is sent)
   - optionally call resetBefore on kernel.request (start of request)

Resetter guideline
- A resetter must be idempotent.
- A resetter must be fast (microseconds/milliseconds).
- No network calls inside reset().

End.
