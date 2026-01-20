Runtime Supercharger — telemetry sink (file snapshot) v01_0

Goal
Expose accurate host-level Prometheus metrics for multi-worker PHP runtimes
without inventing a hybrid runtime.

Model
- Each warm worker keeps RuntimeTelemetry in memory (sketch-21).
- After each request (or at interval), it flushes a snapshot to a shared directory.
- A metrics endpoint/tool reads all snapshots and merges them.

Configuration
- dir: where per-worker snapshots live (must be writable by worker processes)
- workerId: stable id (env var or PID-based)
- flushIntervalSec: throttle for disk IO

Merge rules
- counter: SUM by exact key
- gauge: defaults to MAX
- gauge with metric name suffix _sum or _count: SUM (aggregate semantics)
- worker_start_time_second: MIN (oldest start)
- memory_high_water_byte: MAX
- request_duration_max: MAX

Operational notes
- This is host-scope. For multi-pod, use a shared volume or external sink later.
- Snapshot files are overwritten atomically (write temp -> rename).
- Decode failures are ignored (partial writes / race).
