Reset contract summary

- Any mutable or resource-owning service that may survive across requests MUST implement:
    Symfony\Contracts\Service\ResetInterface

- Such services MUST be tagged:
    runtime.reset

- RuntimeSuperchargerService is responsible for invoking reset for all tagged services after each request,
  and must never stop on a single reset failure.

- Doctrine: EntityManager::clear() is mandatory to avoid identity-map leaks.
