Swoole profile checklist (bridge-agnostic)

Required
- A clear "server entry" file that starts Swoole HTTP server and delegates to Symfony kernel.
- Proper request/response mapping:
  - method, uri, query, headers
  - body stream handling
  - response headers/body/status
- Worker lifecycle policy enabled (sketch-30)
- Worker reset enabled (sketch-31)

Recommended
- Per-worker memory limit + recycle strategy aligned to your SLO window.
- Disable unnecessary global static caches.
- Ensure DB connections are per-request safe (doctrine reset enabled).

Validation
- Run bench + soak harness (sketch-33).
- Check memory curve and error rate; confirm no state leak.
