Runtime Supercharger — Symfony bridge v01_0

Goal
Provide the missing "official-like" lifecycle glue for long-living HTTP workers in Symfony:
- run deterministic resets after each request (terminate phase)
- allow optional container/service reset hook (services_resetter) without hard dependency
- keep policy in Runtime domain (not per business domain)

Components
- RuntimeSuperchargerInterface / RuntimeSupercharger
  Orchestrates: optional vendor reset hook + domain reset registry + optional GC.
- RuntimeSuperchargerSymfonySubscriber
  Hooks into Symfony kernel events and calls RuntimeSupercharger.

Wiring concept
- In long-living workers: prefer reset on kernel.terminate (response already determined).
- Optionally call beforeRequest for symmetry, but terminate is the critical point.

Vendor reset hook ("services_resetter")
- In real Symfony apps there is a service that can reset tagged services.
- This sketch treats it as a generic object with method reset():
    $servicesResetter->reset()

Safety notes
- Resetters must be idempotent and fast.
- Never do network calls in reset.
- Do not call exit() from inside Symfony kernel in FPM mode; recycle decisions belong to worker manager.
