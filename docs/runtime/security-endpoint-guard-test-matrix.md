# Runtime endpoint guard test matrix

## Goal
Lock the runtime endpoint security contract with branch-complete unit coverage for `RuntimeEndpointGuard`.

## Scenario matrix

| Area | Scenario | Input focus | Expected decision |
|---|---|---|---|
| Path filter | Non-runtime path | `/healthz` | `allow: skip` |
| Enabled flag | Explicit disabled variants | `0`, `false`, `no`, `off` | `allow: disabled` |
| Bool-ish edge | Enabled parser baseline | `""`, `"TRUE"`, `"0"`, `"false"` | Keep current parser semantics |
| Mode parser | Unknown mode fallback | `strict`, `soft`, `allowlist`, `denylist`, `""` | Fallback to `allowlist_or_token` |
| allowlist_only | Match / miss | IPv4 allow/miss and IPv6 allow | `allow: ip` / `deny: ipDenied` |
| require_token | Token config missing | `token=""` | `deny: tokenMissingConfig` |
| Token parsing | Missing/empty/malformed/bearer/custom | `Authorization` + custom token header | `tokenMissing`, `tokenDenied`, `allow: token` |
| Proxy strict | Spoofing denied | Forwarded headers + untrusted `REMOTE_ADDR` | `deny: proxyHeaderNotTrusted` |
| Proxy trust | Trusted proxy CIDR accepted | Trusted proxy list CIDR + valid bearer | `allow: token` |
| CIDR/IP parser errors | Invalid CIDR and invalid IP | bad CIDR list + invalid client IP | `deny: ipDenied` |

## Determinism notes

- Tests do not use network calls.
- Trusted proxy globals are backed up and restored per test to prevent cross-test leakage.
- Every branch assertion checks both `allowed` and `reason`.
