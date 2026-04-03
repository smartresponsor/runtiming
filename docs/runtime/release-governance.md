# Runtime Supercharger — Release Governance

## Purpose
Define how the runtime bundle moves from development → RC → release.

## Versioning
- SemVer-like:
  - MAJOR: breaking runtime contracts
  - MINOR: new runtime capabilities
  - PATCH: fixes, CI, docs

## Release checklist
- [ ] CI green (runtime-gate-master + phpstan)
- [ ] No open blocking issues
- [ ] Docs updated (README + docs/runtime)
- [ ] MANIFEST updated
- [ ] Evidence artifacts present in CI

## Tagging
- Tag format: vX.Y.Z
- Tag must correspond to passing CI commit on master

## Changelog (interim)
- Use PR titles as source of truth
- Aggregate into docs/runtime/CHANGELOG.md (future automation)

## Future automation (planned)
- release workflow (GitHub Actions)
- automated changelog generation
- artifact bundling
