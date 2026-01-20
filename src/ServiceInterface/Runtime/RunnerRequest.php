<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

final class RunnerRequest
{
    private string $method;
    private string $path;
    /** @var array<string,string> */
    private array $header;
    private string $body;

    /** @param array<string,string> $header */
    public function __construct(string $method, string $path, array $header = [], string $body = '')
    {
        $this->method = $method;
        $this->path = $path;
        $this->header = $header;
        $this->body = $body;
    }

    public function getMethod(): string { return $this->method; }
    public function getPath(): string { return $this->path; }
    /** @return array<string,string> */
    public function getHeader(): array { return $this->header; }
    public function getBody(): string { return $this->body; }
}
