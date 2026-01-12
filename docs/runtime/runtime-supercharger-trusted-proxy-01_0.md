Runtime Supercharger — trusted proxy posture v01_0

Core rule
- If you are behind a load balancer / reverse proxy, configure Symfony trusted proxies & trusted headers,
  otherwise client IP, scheme, host can be wrong.

Symfony-supported ways
A) Environment variables (recommended)
- SYMFONY_TRUSTED_PROXIES
- SYMFONY_TRUSTED_HEADERS

B) framework.yaml (explicit)
framework:
  trusted_proxies: '192.0.0.1,10.0.0.0/8'
  trusted_headers: ['x-forwarded-for','x-forwarded-host','x-forwarded-proto','x-forwarded-port','x-forwarded-prefix']

Notes and warnings
- Proxy headers can be spoofed. Only trust them when they come from your trusted proxy.
- If you enable x-forwarded-host, ensure your proxy actually sets it (host header attack surface).
- Symfony docs warn that nginx realip module can break trusted proxies behavior: disable it for Symfony apps.

Runtime endpoint specific
- runtime_supercharger.endpoint.security.proxy_strict (default true) denies requests that include proxy headers
  unless REMOTE_ADDR is a trusted proxy.
- This protects against direct-to-app requests with spoofed X-Forwarded-* headers.

Recommended production posture
1) Ingress gate (allowlist/auth) for /metrics + /status
2) Symfony trusted proxies configured (SYMFONY_TRUSTED_PROXIES/HEADERS)
3) Runtime endpoint proxy_strict ON as last line

Quick sanity check
- Request with X-Forwarded-For header sent directly to app (no LB) should be denied (proxyHeaderNotTrusted),
  unless token mode allows it and you set proxy_strict=0.
