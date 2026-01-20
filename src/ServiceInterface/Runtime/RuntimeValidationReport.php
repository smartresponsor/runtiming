<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

final class RuntimeValidationReport
{
    /** @var RuntimeValidationIssue[] */
    private array $issue = [];

    public function add(RuntimeValidationIssue $issue): void
    {
        $this->issue[] = $issue;
    }

    public function isOk(): bool
    {
        return count($this->issue) === 0;
    }

    /** @return RuntimeValidationIssue[] */
    public function getIssue(): array
    {
        return $this->issue;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'ok' => $this->isOk(),
            'issue' => array_map(static fn(RuntimeValidationIssue $i): array => $i->toArray(), $this->issue),
        ];
    }
}
