# Runtime engine recipes (FrankenPHP + RoadRunner)

Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

This is the “Octane equivalent” in Symfony terms:
- Symfony Runtime decouples your front controller from the hosting server, so the same Symfony app can run under long-lived servers (FrankenPHP worker mode, RoadRunner, etc.).
- SmartResponsor keeps the “supercharger” policy inside app code (reset/warm-state rules). This pack only provides engine wiring templates.

1) FrankenPHP (worker mode, Symfony Runtime)
- Install:
  - composer require symfony/runtime runtime/frankenphp-symfony
- Important:
  - Dotenv runs after Symfony Runtime bootstraps; therefore APP_RUNTIME must be provided by the process supervisor/container (not only in .env).
- Minimal run (Docker-style):
  - FRANKENPHP_CONFIG="worker ./public/index.php"
  - APP_RUNTIME=Runtime\FrankenPhpSymfony\Runtime
- Loop safety:
  - Option frankenphp_loop_max can force periodic worker restarts to mitigate leaks.

2) RoadRunner (HTTP, Symfony Runtime)
- Install:
  - composer require symfony/runtime runtime/roadrunner-symfony-nyholm
- Runtime env:
  - APP_RUNTIME=Runtime\RoadRunnerSymfonyNyholm\Runtime
- Minimal rr config:
  - server.command: "php public/index.php"
  - http.address: "0.0.0.0:8081"
  - pool.num_workers chooses worker count.

3) Where Runtime Supercharger hooks in
- Your “supercharger” is app code (subscriber / resetter) that runs on every request end (terminate) and must be deterministic.
- This pack only wires engines; request cleanup policy remains in the Runtime domain code.

4) Choosing defaults (SmartResponsor suggestion)
- FrankenPHP for the “single binary / simplest ops” line, especially for read-heavy components (Category, Order-read, Message-read).
- RoadRunner when you need explicit worker pools and a broader plugin ecosystem.

Files in this pack
- config/runtime/frankenphp/*
- config/runtime/rr/*
- tools/runtime/* (PowerShell helpers, Windows-first)
