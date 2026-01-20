Runtime Supercharger — Benchmark & Soak harness v01_0

Goal
Prove long-running runtime safety and performance under real workload:
- p95 latency targets,
- error-rate targets,
- memory growth bounded,
- recycle/reset behavior observable.

What you get
- tools/runtime/bench/run-bench.ps1: Windows-first runner
- tools/runtime/bench/run-bench.sh: Linux runner
- tools/runtime/bench/sample-status.ps1/.sh: status sampler
- tools/runtime/bench/parse-prom-metrics.py: lightweight prometheus text parser
- report format: CSV + JSON summary

Inputs (env)
- RUNTIME_BENCH_BASE_URL (default http://127.0.0.1:8080)
- RUNTIME_BENCH_ROUTE (default /status)
- RUNTIME_BENCH_WARMUP_COUNT (default 50)
- RUNTIME_BENCH_LOAD_SECOND (default 30)
- RUNTIME_BENCH_SOAK_MINUTE (default 10)
- RUNTIME_BENCH_CONCURRENCY (default 8)  (curl loop uses it as parallel jobs)
- RUNTIME_BENCH_SAMPLE_SECOND (default 5)

Outputs
- report/runtime/bench/<stamp>/summary.json
- report/runtime/bench/<stamp>/status.csv
- report/runtime/bench/<stamp>/metrics.json  (if /metrics available)

Success criteria (suggested)
- error rate: <= 0.5% for load and soak
- memory usage MB: stable trend (no monotonic growth beyond +10%)
- recycle events: occur when configured (max_request/max_memory/max_uptime)

Notes
- Curl mode is portable but simplistic; for accurate p95 use k6 mode (optional).
- If /metrics is secured, provide token header in RUNTIME_BENCH_TOKEN and RUNTIME_BENCH_TOKEN_HEADER.
