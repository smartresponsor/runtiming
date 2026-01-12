<?php
declare(strict_types=1);

/*
Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
*/

namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeKernelResetInterface;

final class RuntimeKernelResetAdapter implements RuntimeKernelResetInterface
{
    private object $inner;
    private string $method;

    public function __construct(object $inner, string $method = 'reset')
    {
        $this->inner = $inner;
        $this->method = $method;
    }

    public function reset(): void
    {
        $method = $this->method;

        if (!method_exists($this->inner, $method)) {
            $type = get_debug_type($this->inner);

            throw new \RuntimeException(
                sprintf('Kernel reset adapter expects method "%s" on "%s".', $method, $type)
            );
        }

        $this->inner->{$method}();
    }
}
