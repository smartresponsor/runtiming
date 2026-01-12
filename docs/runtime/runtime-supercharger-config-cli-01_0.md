Runtime Supercharger — config + CLI v01_0

Goal
Provide a single canonical configuration surface for "Runtime Supercharger" across services:
- reset toggles
- worker limit (recycle policy)
- event feed sink settings (NDJSON path + rotation)

Env keys (default provider)
- RUNTIME_SUPERCHARGER_BEFORE_ENABLE (bool, default: 0)
- RUNTIME_SUPERCHARGER_AFTER_ENABLE  (bool, default: 1)
- RUNTIME_SUPERCHARGER_GC_ENABLE     (bool, default: 0)

- RUNTIME_SUPERCHARGER_MAX_REQUEST    (int, default: 3000)
- RUNTIME_SUPERCHARGER_MAX_UPTIME_SEC (int, default: 900)
- RUNTIME_SUPERCHARGER_SOFT_MEMORY_MB (int, default: 256)
- RUNTIME_SUPERCHARGER_MAX_MEMORY_MB  (int, default: 384)

- RUNTIME_SUPERCHARGER_FEED_PATH      (string, default: var/runtime/runtime-supercharger-feed.ndjson)
- RUNTIME_SUPERCHARGER_FEED_MAX_BYTES (int, default: 10485760)
- RUNTIME_SUPERCHARGER_FEED_MAX_KEEP  (int, default: 20)

Validation rules (validator)
- numeric values must be positive (softMemory may be 0)
- softMemoryMb must be <= maxMemoryMb
- feed path must be non-empty and end with .ndjson
- feedMaxBytes must be >= 1024
- feedMaxKeep is 0..200 (0 means "do not trim")

Integration idea (later sketches)
- Use this config in sketch-8 subscriber wiring, sketch-9 worker supervisor, sketch-10 sink.
