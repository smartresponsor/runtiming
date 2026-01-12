Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
#!/usr/bin/env python3
import csv, json, sys, os, statistics

def main():
    if len(sys.argv) < 2:
        print("usage: aggregate-status.py <status.csv> [summary.json]", file=sys.stderr)
        return 2

    status_path = sys.argv[1]
    summary_path = sys.argv[2] if len(sys.argv) > 2 else None

    rows = []
    with open(status_path, "r", encoding="utf-8") as f:
        r = csv.DictReader(f)
        for row in r:
            try:
                code = int(row.get("http_code","0") or "0")
                ms = int(row.get("ms","0") or "0")
            except Exception:
                continue
            rows.append((code, ms))

    if not rows:
        out = {"count": 0}
    else:
        codes = [c for c,_ in rows]
        mss = [m for _,m in rows if m >= 0]
        ok = sum(1 for c in codes if 200 <= c < 500)  # treat 4xx as ok for availability view
        fail = sum(1 for c in codes if c == 0 or c >= 500)
        p50 = int(statistics.median(mss)) if mss else 0
        p95 = int(statistics.quantiles(mss, n=20)[-1]) if len(mss) >= 20 else max(mss) if mss else 0
        out = {
            "count": len(rows),
            "ok": ok,
            "fail": fail,
            "failRate": (fail / len(rows)) if len(rows) else 0.0,
            "latencyMs": {"p50": p50, "p95": p95, "max": max(mss) if mss else 0}
        }

    if summary_path and os.path.isfile(summary_path):
        with open(summary_path, "r", encoding="utf-8") as f:
            summary = json.load(f)
    else:
        summary = {}

    summary["statusSample"] = out
    json.dump(summary, sys.stdout, indent=2)
    sys.stdout.write("\n")
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
