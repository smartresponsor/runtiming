Runtime Supercharger observability v01_0

0. Metrics (required)
These metrics must exist in every environment where Runtime Supercharger is enabled:

- runtime_worker_restart_total{reason}
    Incremented each time a worker is recycled/restarted. Reason labels:
    max_requests | mem_limit | consecutive_failures | manual | unknown

- runtime_worker_request_total
    Total requests served by a worker group.

- runtime_worker_mem_bytes
    Current resident memory per worker (or aggregate).

- runtime_recycle_duration_ms
    Time between recycle decision and new worker ready.

1. Collector approach
In docker/stage you can collect worker memory and restarts from docker stats
and export them in Prometheus textfile format. See tool/runtime/collect-runtime-worker-metric.sh.

In Kubernetes, prefer native sources:
- cgroup memory from kubelet / cadvisor
- container restarts from kube-state-metrics
Map them into the same metric names via recording rules.

2. Alerts (required)
- memory growth risk: worker_mem_bytes trending up without plateau
- restart storm: restart_total rate too high
- recycle latency: recycle_duration_ms p95 above SLO

rules are in ops/runtime/prometheus/runtime-supercharger-alert-rule.yaml.

3. Dashboard (required)
Grafana dashboard JSON is in ops/runtime/grafana/runtime-supercharger-dashboard.json.
It expects the above metric names to exist.

4. CI perf gate
tool/runtime/ci-perf-gate.sh compares p95 vs baseline and fails if regression is detected.
It is designed to be called from your existing CI with exported baseline values.

End.
