Runtime Supercharger — wrapper entrypoint v01_0

What this gives you
- An integration point you can own inside SmartResponsor Runtime domain:
  - one place to define worker loop/exit semantics
  - consistent behavior across FrankenPHP / RoadRunner
  - easy to test locally (CLI loop)

Production strategy
- In production, you run an engine (rr/franken/swoole).
- The engine invokes your wrapper for each worker.
- The wrapper exits after N requests or when "Recycle" header is set.
- Engine restarts fresh worker.

Notes
- In real RoadRunner integration, you would parse PSR-7 request from the engine and send PSR-7 response back.
- In real FrankenPHP integration, you might rely on its worker mode and still use wrapper hooks after response.

This sketch intentionally avoids vendor SDKs to keep the Runtime domain pure and portable.
