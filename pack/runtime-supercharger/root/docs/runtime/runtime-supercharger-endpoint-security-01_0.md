Runtime Supercharger — endpoint security v01_0

Goal
Harden /metrics and /status endpoints without forcing framework-level "hybrids".
We keep routing explicit (you import routes) and we keep exposure intentional, but if exposed,
we still gate by default (loopback-only allowlist).

Recommended production posture
1) Keep routes imported only in the environment where needed.
2) Additionally gate at ingress (allowlist / auth).
3) Keep application-level gate enabled as a last line of defense.

Config (bundle root)
runtime_supercharger:
  endpoint:
    security:
      enabled: true
      mode: allowlist_or_token        # allowlist_or_token | allowlist_only | require_token
      allow_cidr: [ "10.0.0.0/8" ]    # list of CIDR strings
      token: "%env(RUNTIME_ENDPOINT_TOKEN)%"
      header: "X-Runtime-Token"

Ingress example (concept)
- allow /metrics and /status only from monitoring network, or require an auth token.
- ensure Symfony trusted proxies are set correctly if you rely on X-Forwarded-For.

Operational checklist
- /metrics returns 200 only for allowed callers.
- /status returns 200 only for allowed callers.
- denied calls return HTTP 403 with JSON body.
- telemetry snapshot dir is not exposed via web server.

Notes
- Client IP depends on Symfony trusted proxy config when behind LB/ingress.
- Token is accepted from:
  - Authorization: Bearer <token>
  - <header> (default X-Runtime-Token): <token>
