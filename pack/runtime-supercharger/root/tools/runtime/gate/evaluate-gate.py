#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import csv, json, os, sys, statistics
from typing import List

def quantile_p95(values: List[int]) -> int:
    if not values:
        return 0
    values = sorted(values)
    if len(values) < 20:
        return int(values[-1])
    # p95 as nearest-rank
    k = int((0.95 * len(values)) + 0.999999)  # ceil
    k = max(1, min(len(values), k))
    return int(values[k - 1])

def main() -> int:
    if len(sys.argv) < 2:
        print("usage: evaluate-gate.py <status.csv> [summary.json]", file=sys.stderr)
        return 2

    status_path = sys.argv[1]
    summary_path = sys.argv[2] if len(sys.argv) > 2 else None

    p95_max = float(os.environ.get("RUNTIME_GATE_P95_MS_MAX", "250"))
    fail_rate_max = float(os.environ.get("RUNTIME_GATE_FAIL_RATE_MAX", "0.005"))

    ms_values: List[int] = []
    count = 0
    fail = 0

    with open(status_path, "r", encoding="utf-8") as f:
        r = csv.DictReader(f)
        for row in r:
            count += 1
            try:
                code = int(row.get("http_code", "0") or "0")
                ms = int(row.get("ms", "0") or "0")
            except Exception:
                continue

            if ms >= 0:
                ms_values.append(ms)

            # fail if 0 (transport) or 5xx
            if code == 0 or code >= 500:
                fail += 1

    fail_rate = (fail / count) if count else 1.0
    p95 = quantile_p95(ms_values)
    out = {
        "count": count,
        "fail": fail,
        "failRate": fail_rate,
        "latencyMs": {"p95": p95, "p95Max": p95_max},
        "threshold": {"failRateMax": fail_rate_max},
        "pass": (p95 <= p95_max and fail_rate <= fail_rate_max),
    }

    if summary_path and os.path.isfile(summary_path):
        try:
            with open(summary_path, "r", encoding="utf-8") as f:
                summary = json.load(f)
            out["bench"] = summary
        except Exception:
            pass

    sys.stdout.write(json.dumps(out, indent=2) + "\n")
    return 0 if out["pass"] else 1

if __name__ == "__main__":
    raise SystemExit(main())
