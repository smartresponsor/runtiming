Runtime Supercharger — Symfony lifecycle v01_0

What this adds
- A canonical EventSubscriber that attaches to Symfony kernel events:
  - REQUEST: start timer + warm state increment
  - RESPONSE: compute worker decision, emit decision event/metric, set headers when recycle is recommended
  - TERMINATE: run reset + gc, emit reset event/metric, export Prometheus textfile

Why split RESPONSE vs TERMINATE
- RESPONSE is still before the response is sent: you can add headers.
- TERMINATE occurs after send: you can do heavier cleanup (reset/gc/export) without affecting TTFB.

Engine / recycle
- This sketch does not stop workers directly.
- It only signals recycle via headers. Engine recipes/wrappers can interpret the header and exit gracefully.

Memory snapshot
- Uses memory_get_usage(true) as a portable proxy (not real RSS).
