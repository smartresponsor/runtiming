<?php

/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp.
 */

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/test',
        __DIR__ . '/tools',
        __DIR__ . '/tool',
        __DIR__ . '/runtime',
        __DIR__ . '/worker',
    ])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder);
