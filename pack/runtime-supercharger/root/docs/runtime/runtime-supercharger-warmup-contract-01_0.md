Warmup contract summary

- Warmup MUST run only at worker boot.
- Warmup list MUST be declared in config, not hardcoded in domains.
- Any warmer MUST be pure or reset-safe and MUST NOT keep request-scoped state.
- Warmers are Symfony services tagged:
    runtime.warm
