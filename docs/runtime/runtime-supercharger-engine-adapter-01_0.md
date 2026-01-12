Runtime Supercharger — engine adapter v01_0

Goal
Provide a minimal abstraction so that "recycle now" can be executed by different runtimes.

Contract
RuntimeEngineAdapterInterface::plan($decision, $header) -> RuntimeEngineAction
Action types:
- none
- gracefulExit (recommended recycle)
- hardExit (safety, optional)

Policies (default)
- If decision says shouldRecycle => gracefulExit.
- If reason contains "memory" or "maxMemory" => hardExit only if config flag is enabled.
- Else => gracefulExit.

Integration patterns
1) Wrapper process
- Your entrypoint calls Symfony and sends response.
- After response, read decision/header and perform action:
  - gracefulExit => exit(0)
  - hardExit => exit(1)

2) Engine hooks
- RoadRunner / FrankenPHP generally restart workers on exit.
