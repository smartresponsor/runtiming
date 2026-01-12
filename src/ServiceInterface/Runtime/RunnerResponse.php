<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RunnerResponse
{
    private int $status;

    /** @var array<string,string> */
    private array $header;

    private string $body;

    /** @param array<string,string> $header */
    public function __construct(int $status, array $header = [], string $body = '')
    {
        $this->status = $status;
        $this->header = $header;
        $this->body = $body;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /** @return array<string,string> */
    public function getHeader(): array
    {
        return $this->header;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function withHeader(string $name, string $value): self
    {
        $h = $this->header;
        $h[$name] = $value;
        return new self($this->status, $h, $this->body);
    }
}
