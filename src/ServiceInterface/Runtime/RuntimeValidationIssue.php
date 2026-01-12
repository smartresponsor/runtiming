<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeValidationIssue
{
    private string $code;
    private string $message;

    /** @var array<string,mixed> */
    private array $meta;

    /**
     * @param array<string,mixed> $meta
     */
    public function __construct(string $code, string $message, array $meta = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->meta = $meta;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /** @return array<string,mixed> */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'meta' => $this->meta,
        ];
    }
}
