Runtime Supercharger — Swoole profile v01_0

Goal
Document and standardize a Swoole runtime profile for Symfony 8:
- long-running workers,
- safe warm state via reset+recycle,
- endpoint security consistent with other engines.

Why this is different from RR/FrankenPHP
- RR and FrankenPHP provide more "drop-in" worker models for PHP apps.
- Swoole requires a bridge layer (HTTP server integration) and is more intrusive.
- We treat it as optional / advanced profile.

What you get
- env template aligned with Supercharger worker lifecycle + reset and endpoint security.
- docker-compose snippet to run a Swoole container with required extensions.
- checklist for bridge choice (e.g., Symfony Runtime + Swoole bridge, or custom server).

Bridge choices (overview)
- Option A: Symfony Runtime integration for Swoole (if you use symfony/runtime).
- Option B: A dedicated Swoole HTTP server that forwards to Symfony kernel.
- Option C: Use a proven bundle/bridge and only standardize env + reset policies.

This sketch stays neutral because SmartResponsor is not a framework product; it’s for your service ops.

Quick-start (conceptual)
1) Ensure swoole extension is installed (PHP 8.5 compatible).
2) Choose a bridge strategy and implement it in your host app.
3) Apply env template and keep reset/lifecycle enabled.
4) Use bench harness (sketch-33) to validate behavior.

Security posture
- Do not expose /metrics publicly; use allowlist or token.
- Ensure trusted proxy headers are validated (sketch-29).

Notes
- Keep this profile documented even if you ship RR/FrankenPHP as primary.
