Runtime Supercharger — resetter library v01_0

Goal
In warm workers, correctness depends on reset discipline.
This library provides:
- RuntimeResetterChain: run multiple resetters, keep last report.
- RuntimeContainerServiceResetter: reset a configured allow-list of services from the container.

Typical usage (wrapper entrypoint)
- Build a chain:
  $chain = new RuntimeResetterChain([
      new RuntimeSymfonyResetter(true), // sketch-17
      new RuntimeContainerServiceResetter([
          'doctrine.orm.entity_manager',
          'doctrine.dbal.default_connection',
          'cache.app',
          'cache.system',
          'http_client',
      ]),
  ]);

- Pass the chain to RuntimeSymfonyRunner (sketch-17).

Reset strategy (per service)
1) If service implements Symfony ResetInterface (or has reset()): call reset().
2) If service looks like Doctrine EntityManager: clear(), and close() if supported.
3) If service looks like DBAL Connection: close() if supported.
4) If service looks like cache pool: clear().
5) Otherwise: record "skip".

Why allow-list
- Product-grade warm workers must avoid resetting arbitrary services blindly.
- The allow-list makes behavior explicit, testable, and reviewable.

Report
- Each reset attempt is recorded with:
  - id, type, action, ok, note
- RuntimeResetterChain exposes getLastReportAsArray().
