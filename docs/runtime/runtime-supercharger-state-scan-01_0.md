Runtime Supercharger — state scan v01_0

Why
Long-living workers keep PHP processes alive across requests. Any mutable singleton/static state can leak data,
accumulate memory, or cross-contaminate security/auth contexts. A simple scan catches most of the common risks.

What it detects (rules)
- RS001 static properties in classes (public/protected/private static $x)
- RS002 singleton patterns (private static $instance, getInstance())
- RS003 superglobals usage ($_SESSION, $_SERVER, $_ENV, $_COOKIE)
- RS004 destructors (__destruct) in service/infra layers (risk of resource leakage patterns)
- RS005 persistent buffers (in-memory arrays/maps with "cache|registry|buffer|map" names; heuristic)

Output contract
Each finding line is NDJSON:
  {"ruleId":"RS001","severity":"warning|error","file":"...","line":123,"message":"...","excerpt":"..."}

CI gate
- FailOn=error: exit 1 if any error exists.
- FailOn=warning: exit 1 if any warning OR error exists.

Notes
- This is intentionally conservative. Some findings are false positives.
- Treat the report as a to-do list: add ResetInterface, refactor state, or tighten recycle.
