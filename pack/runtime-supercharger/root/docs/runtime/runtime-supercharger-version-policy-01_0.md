Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
Runtime Supercharger — Version policy v01_0

Target posture
- Symfony: 8.x (including dev branches if you choose)
- PHP: nightly / master (future 9.x readiness), while remaining compatible with current stable where possible
- Runtime engines: RoadRunner / FrankenPHP as primary options

Practical rules
- Do not hardcode engine behaviors to a single minor version.
- Keep gate scripts dependency-free (PowerShell + bash + Python standard library).
- Prefer feature detection over version detection.

Composer advice (host app)
- If you test against dev stacks:
  - minimum-stability: dev
  - prefer-stable: true
- Use platform.php carefully:
  - Setting platform.php to an unreleased major can block installs.
  - Prefer running CI on nightly PHP instead of faking platform to 9.x.

CI advice
- Use at least two lanes:
  - stable lane (current stable PHP + stable deps)
  - nightly lane (PHP nightly + Symfony dev if desired)
- Gate must pass on stable lane to ship.
- Nightly lane is "early warning"; treat failures as tasks, not immediate ship blocks (unless you choose otherwise).
