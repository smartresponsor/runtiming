<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

final class RuntimeWorkerRecycleHeader
{
    public const RECYCLE = 'X-Runtime-Supercharger-Recycle';
    public const ACTION = 'X-Runtime-Supercharger-Action';
    public const REASON = 'X-Runtime-Supercharger-Reason';

    public const ACTION_GRACEFUL = 'gracefulExit';
    public const ACTION_HARD = 'hardExit';
}
