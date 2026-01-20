<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeEngineAdapterInterface;

final class RuntimeFrankenPhpEngineAdapter extends RuntimeEngineWrapper
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
        // FrankenPHP wrapper: just exit to recycle the worker.
        $action = $this->adapter->plan($decision, $header);
        $this->apply($action);
    }
}
