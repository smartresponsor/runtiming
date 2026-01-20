<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeSuperchargerConfigInterface;

final class RuntimeSuperchargerConfig implements RuntimeSuperchargerConfigInterface
{
    private bool $beforeEnable;
    private bool $afterEnable;
    private bool $gcEnable;

    private int $maxRequest;
    private int $maxUptimeSec;
    private int $softMemoryMb;
    private int $maxMemoryMb;

    private string $feedPath;
    private int $feedMaxBytes;
    private int $feedMaxKeep;

    public function __construct(
        bool $beforeEnable,
        bool $afterEnable,
        bool $gcEnable,
        int $maxRequest,
        int $maxUptimeSec,
        int $softMemoryMb,
        int $maxMemoryMb,
        string $feedPath,
        int $feedMaxBytes,
        int $feedMaxKeep
    ) {
        $this->beforeEnable = $beforeEnable;
        $this->afterEnable = $afterEnable;
        $this->gcEnable = $gcEnable;

        $this->maxRequest = $maxRequest;
        $this->maxUptimeSec = $maxUptimeSec;
        $this->softMemoryMb = $softMemoryMb;
        $this->maxMemoryMb = $maxMemoryMb;

        $this->feedPath = $feedPath;
        $this->feedMaxBytes = $feedMaxBytes;
        $this->feedMaxKeep = $feedMaxKeep;
    }

    public static function default(): self
    {
        return new self(
            false,
            true,
            false,
            3000,
            900,
            256,
            384,
            'var/runtime/runtime-supercharger-feed.ndjson',
            10 * 1024 * 1024,
            20
        );
    }

    /** @param array<string,mixed> $a */
    public static function fromArray(array $a): self
    {
        $d = self::default();

        return new self(
            (bool) ($a['beforeEnable'] ?? $d->getBeforeEnable()),
            (bool) ($a['afterEnable'] ?? $d->getAfterEnable()),
            (bool) ($a['gcEnable'] ?? $d->getGcEnable()),
            (int) ($a['maxRequest'] ?? $d->getMaxRequest()),
            (int) ($a['maxUptimeSec'] ?? $d->getMaxUptimeSec()),
            (int) ($a['softMemoryMb'] ?? $d->getSoftMemoryMb()),
            (int) ($a['maxMemoryMb'] ?? $d->getMaxMemoryMb()),
            (string) ($a['feedPath'] ?? $d->getFeedPath()),
            (int) ($a['feedMaxBytes'] ?? $d->getFeedMaxBytes()),
            (int) ($a['feedMaxKeep'] ?? $d->getFeedMaxKeep()),
        );
    }

    public function getBeforeEnable(): bool { return $this->beforeEnable; }

    public function getAfterEnable(): bool { return $this->afterEnable; }

    public function getGcEnable(): bool { return $this->gcEnable; }

    public function getMaxRequest(): int { return $this->maxRequest; }

    public function getMaxUptimeSec(): int { return $this->maxUptimeSec; }

    public function getSoftMemoryMb(): int { return $this->softMemoryMb; }

    public function getMaxMemoryMb(): int { return $this->maxMemoryMb; }

    public function getFeedPath(): string { return $this->feedPath; }

    public function getFeedMaxBytes(): int { return $this->feedMaxBytes; }

    public function getFeedMaxKeep(): int { return $this->feedMaxKeep; }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'beforeEnable' => $this->beforeEnable,
            'afterEnable' => $this->afterEnable,
            'gcEnable' => $this->gcEnable,
            'maxRequest' => $this->maxRequest,
            'maxUptimeSec' => $this->maxUptimeSec,
            'softMemoryMb' => $this->softMemoryMb,
            'maxMemoryMb' => $this->maxMemoryMb,
            'feedPath' => $this->feedPath,
            'feedMaxBytes' => $this->feedMaxBytes,
            'feedMaxKeep' => $this->feedMaxKeep,
        ];
    }
}
