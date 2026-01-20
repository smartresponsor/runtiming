<?php
declare(strict_types=1);



namespace App\ServiceInterface\Runtime;

use DateTimeImmutable;
use DateTimeZone;

final class RuntimeSuperchargerEvent
{
    private string $type;

    /** @var array<string,mixed> */
    private array $payload;

    /** @var float */
    private float $ts;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(string $type, array $payload, ?float $ts = null)
    {
        $this->type = $type;
        $this->payload = $payload;
        $this->ts = $ts ?? microtime(true);
    }

    public function getType(): string
    {
        return $this->type;
    }

    /** @return array<string,mixed> */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        $ms = (int) floor(($this->ts - floor($this->ts)) * 1000);
        $dt = (new DateTimeImmutable('@' . (string) floor($this->ts)))
            ->setTimezone(new DateTimeZone('UTC'));

        return [
            'type' => $this->type,
            'ts' => $dt->format('Y-m-d\\TH:i:s') . '.' . str_pad((string) $ms, 3, '0', STR_PAD_LEFT) . 'Z',
            'payload' => $this->payload,
        ];
    }
}
