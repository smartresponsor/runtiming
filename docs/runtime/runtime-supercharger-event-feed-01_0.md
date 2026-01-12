Runtime Supercharger — event feed v01_0

Goal
In long-living workers you need a cheap, append-only trace of lifecycle decisions:
- what was reset after each request (reset report)
- whether the worker should be recycled (decision + reason)

This feed is local and neutral:
- NDJSON (one JSON object per line)
- file rotation by size
- no external dependency

Event types
- runtime.reset
  payload: resetReport (from RuntimeResetReport::toArray if available)
- runtime.workerDecision
  payload: decision (RuntimeWorkerDecision::toArray)

Integration points
- Decorate RuntimeResetRegistryInterface service with RuntimeResetRegistryEventDecorator.
- Decorate RuntimeWorkerSupervisorInterface service with RuntimeWorkerSupervisorEventDecorator.

Sink config (RuntimeNdjsonFileSink)
- path: <var>/runtime/runtime-supercharger-feed.ndjson
- maxBytes: rotate when current file >= maxBytes
- maxKeep: number of rotated files to keep

Safety notes
- emitting must never throw (best-effort)
- never do network calls from decorators
