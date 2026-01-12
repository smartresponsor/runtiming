Runtime Supercharger — Incident playbook v01_0

P0 warm-state leak / security issue
1) Immediately reduce blast radius:
   - Route traffic to safe pool (FPM) if available, or cut traffic.
2) Mitigate:
   - MAX_REQUEST=10..50
   - MAX_UPTIME=300..600
   - Ensure reset enabled (kernel/doctrine).
3) Confirm:
   - Run a repeated request scenario to reproduce.
4) Preserve evidence:
   - Save status.csv + summary.json + logs.
5) Fix:
   - Identify offending global cache/static and reset or remove.
6) Prevent recurrence:
   - Add regression test in bench harness or app tests.

P1 performance regression
1) Compare p95 and error rate to last baseline report.
2) Validate engine config (RR/FrankenPHP) did not change unexpectedly.
3) Check worker restart thrash / OOM kills.
4) Tune:
   - lower num_workers or memory limits
   - adjust recycle thresholds
