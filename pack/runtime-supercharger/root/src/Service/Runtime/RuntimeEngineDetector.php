<?php
declare(strict_types=1);



namespace App\Service\Runtime;

use App\ServiceInterface\Runtime\RuntimeEngineDetectorInterface;

final class RuntimeEngineDetector implements RuntimeEngineDetectorInterface
{
    private string $engine;

    public function __construct(string $engine)
    {
        $this->engine = trim($engine);
    }

    public function getEngineName(): string
    {
        if ($this->engine !== '') {
            return $this->engine;
        }

        // Best-effort detection by sapi/env.
        if (PHP_SAPI === 'cli') {
            return 'cli';
        }

        if (PHP_SAPI === 'fpm-fcgi') {
            return 'fpm';
        }

        return PHP_SAPI;
    }
}
