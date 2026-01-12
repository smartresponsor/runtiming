Runtime Supercharger — Debug checklist v01_0

When something looks wrong under long-running runtime:

Checks
- Is reset enabled? (RUNTIME_WORKER_RESET_ENABLED=1)
- Is kernel reset active? (RUNTIME_WORKER_RESET_KERNEL=1) and services_resetter exists
- Is doctrine reset active? (RUNTIME_WORKER_RESET_DOCTRINE=1)
- Are lifecycle thresholds sensible for pod limits?
- Are trusted proxies configured correctly?
- Are endpoints secured (allowlist/token)?

Common culprits
- static caches inside services
- singleton client storing request-specific headers
- non-reset Doctrine EntityManager identity map
- in-memory per-request context accidentally stored on a shared service

Fix patterns
- implement RuntimeResetterInterface for the service and register tag runtime_supercharger_resetter
- move request context to RequestStack or per-request DTO
- ensure HTTP clients are stateless or reset between requests
