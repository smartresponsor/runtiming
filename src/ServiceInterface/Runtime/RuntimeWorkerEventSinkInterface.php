<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);




namespace App\ServiceInterface\Runtime;

use App\Service\Runtime\RuntimeWorkerDecision;

interface RuntimeWorkerEventSinkInterface
{
    public function onDecision(RuntimeWorkerDecision $decision): void;
}
