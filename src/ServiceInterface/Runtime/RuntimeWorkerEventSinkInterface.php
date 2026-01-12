<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

use App\Service\Runtime\RuntimeWorkerDecision;

interface RuntimeWorkerEventSinkInterface
{
    public function onDecision(RuntimeWorkerDecision $decision): void;
}
