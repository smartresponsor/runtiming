#!/usr/bin/env python3
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import csv, json, os, sys
from typing import List, Dict, Any

def quantile_p95(values: List[int]) -> int:
    if not values:
        return 0
    values = sorted(values)
    k = int((0.95 * len(values)) + 0.999999)  # ceil nearest-rank
    k = max(1, min(len(values), k))
    return int(values[k - 1])

def eval_csv(path: str) -> Dict[str, Any]:
    ms_values: List[int] = []
    count = 0
    fail = 0
    with open(path, "r", encoding="utf-8") as f:
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
            if code == 0 or code >= 500:
                fail += 1
    fail_rate = (fail / count) if count else 1.0
    p95 = quantile_p95(ms_values)
    return {
        "count": count,
        "fail": fail,
        "failRate": fail_rate,
        "latencyMs": {"p95": p95, "max": (max(ms_values) if ms_values else 0)},
    }

def main() -> int:
    if len(sys.argv) < 2:
        print("usage: evaluate-multi-gate.py <csv1> [csv2..]", file=sys.stderr)
        return 2

    p95_max = float(os.environ.get("RUNTIME_GATE_P95_MS_MAX", "250"))
    fail_rate_max = float(os.environ.get("RUNTIME_GATE_FAIL_RATE_MAX", "0.005"))

    items = []
    ok_all = True

    for p in sys.argv[1:]:
        e = eval_csv(p)
        e["file"] = p
        e["threshold"] = {"p95MsMax": p95_max, "failRateMax": fail_rate_max}
        e["pass"] = (e["latencyMs"]["p95"] <= p95_max and e["failRate"] <= fail_rate_max)
        if not e["pass"]:
            ok_all = False
        items.append(e)

    out = {"pass": ok_all, "route": items}
    sys.stdout.write(json.dumps(out, indent=2) + "\n")
    return 0 if ok_all else 1

if __name__ == "__main__":
    raise SystemExit(main())
