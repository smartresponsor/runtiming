# Runtime env ordering note

Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

Why we export APP_RUNTIME from the runner:
- Symfony Runtime runs before Dotenv. If APP_RUNTIME is only in .env, it may not be available early enough.

Practical rule:
- In Docker: set APP_RUNTIME (and engine vars) via `environment:`.
- In CI/host: export APP_RUNTIME at process start (systemd service env, container env, etc.).
