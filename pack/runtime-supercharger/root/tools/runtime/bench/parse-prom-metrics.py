# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
#!/usr/bin/env python3
import re, json, sys, time
from typing import Dict, Any, Tuple

# Minimal Prometheus text format parser:
# - ignores HELP/TYPE
# - parses: metric_name{label="x"} 123.4
# - keeps last sample per unique (name,labels)

line_re = re.compile(r'^([a-zA-Z_:][a-zA-Z0-9_:]*)(\{[^}]*\})?\s+([-+]?[0-9]*\.?[0-9]+(?:[eE][-+]?[0-9]+)?)$')

def parse_labels(lbl: str) -> Dict[str,str]:
    if not lbl:
        return {}
    lbl = lbl.strip()
    if not (lbl.startswith("{") and lbl.endswith("}")):
        return {}
    inner = lbl[1:-1].strip()
    if inner == "":
        return {}
    out = {}
    # naive split by , not inside quotes (good enough for typical labels)
    parts = re.split(r',(?=(?:[^"]*"[^"]*")*[^"]*$)', inner)
    for p in parts:
        p = p.strip()
        if p == "" or "=" not in p:
            continue
        k, v = p.split("=", 1)
        k = k.strip()
        v = v.strip()
        if len(v) >= 2 and v[0] == '"' and v[-1] == '"':
            v = v[1:-1]
        out[k] = v
    return out

def key(name: str, labels: Dict[str,str]) -> str:
    if not labels:
        return name
    items = ",".join([f"{k}={labels[k]}" for k in sorted(labels.keys())])
    return f"{name}{{{items}}}"

def main() -> int:
    data = sys.stdin.read().splitlines()
    out: Dict[str, Any] = {}
    out["ts"] = int(time.time())
    out["metric"] = {}
    for ln in data:
        ln = ln.strip()
        if ln == "" or ln.startswith("#"):
            continue
        m = line_re.match(ln)
        if not m:
            continue
        name, lbl, val = m.group(1), m.group(2) or "", m.group(3)
        labels = parse_labels(lbl)
        k = key(name, labels)
        try:
            v = float(val)
        except Exception:
            continue
        out["metric"][k] = v
    sys.stdout.write(json.dumps(out, indent=2) + "\n")
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
