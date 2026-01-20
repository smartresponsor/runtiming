<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infra\Runtime;

use App\RuntimeInterface\RuntimeWarmerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Example safe warmer: preloads Doctrine metadata caches.
 * Must NOT touch UnitOfWork or keep managed entities.
 */
final class DoctrineMetadataWarmer implements RuntimeWarmerInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function warm(): void
    {
        // Touch metadata factory to populate metadata caches.
        $this->entityManager->getMetadataFactory()->getAllMetadata();
    }
}
