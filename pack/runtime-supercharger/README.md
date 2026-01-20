# Runtime Supercharger Pack

This pack is a **drop-in** bundle for SmartResponsor/Symfony repositories.

What it installs:
- Runtime endpoints: `/metrics`, `/status/host`, `/status/worker`
- Guarded access to runtime endpoints (secure-default)
- Worker lifecycle telemetry + file snapshot sink
- Reset chain and safe resetters (Doctrine/Container)
- CI gate for runtime health (optional)

## Apply (PowerShell)

From the **target repo root**:

```powershell
pwsh -NoProfile -ExecutionPolicy Bypass -File .\pack\runtime-supercharger\apply.ps1 -TargetRoot .
```

## Apply (Bash)

```bash
bash ./pack/runtime-supercharger/apply.sh .
```

## Notes
- The pack copies files into the target repo root.
- Existing files are backed up into `.pack-backup/<timestamp>/...`.
- If you want to disable runtime routes in production, set the guard to deny-all and expose via internal network only.
