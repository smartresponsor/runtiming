# Runtime Supercharger k6 Bench (02_0)

This pack provides k6 scripts for quick RC baseline/soak/spike checks.

## Files

- `tools/k6/runtime-baseline.js` – short baseline
- `tools/k6/runtime-soak.js` – long soak
- `tools/k6/runtime-spike.js` – spike ramp
- `tools/k6/thresholds-runtime.json` – default thresholds
- `tools/k6/run-k6-runtime.sh` / `.ps1` – runner

## Usage

Baseline (30s, 5 VUs):

```bash
tools/k6/run-k6-runtime.sh http://127.0.0.1:8080 baseline
```

Soak (15m, 10 VUs):

```bash
tools/k6/run-k6-runtime.sh http://127.0.0.1:8080 soak
```

Spike (default p95<1200ms):

```bash
BASE_URL=http://127.0.0.1:8080 VUS_SPIKE=50 k6 run tools/k6/runtime-spike.js
```

Windows:

```powershell
.\tools\k6\run-k6-runtime.ps1 -BaseUrl http://127.0.0.1:8080 -Mode baseline
```

## Notes

- Scripts hit `/status/worker`, `/status/host` and occasionally `/metrics`.
- Use them as a safe smoke/perf gate before production traffic.
