<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeEngineAdapterInterface;

/**
 * No direct Swoole API calls in this sketch to avoid hard dependency.
 * In real integration, you'd call $server->shutdown() or worker exit hooks.
 */
final class RuntimeSwooleEngineAdapter extends RuntimeEngineWrapper
{
    private RuntimeEngineAdapterInterface $adapter;

    public function __construct(RuntimeEngineAdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param array<string,string> $header
     */
    public function afterResponse(?object $decision, array $header = []): void
    {
        $action = $this->adapter->plan($decision, $header);
        $this->apply($action);
    }
}
