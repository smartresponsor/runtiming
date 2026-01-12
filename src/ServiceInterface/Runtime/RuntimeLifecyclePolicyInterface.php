<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\ServiceInterface\Runtime;

interface RuntimeLifecyclePolicyInterface
{
    public function boot(): void;

    public function beforeRequest(): void;

    public function afterRequest(): RuntimeLifecycleDecision;

    public function getState(): RuntimeLifecycleState;
}
