<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infra\Runtime;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Resets Doctrine UnitOfWork and identity map for long-living workers.
 */
final class DoctrineEntityManagerResetter implements ResetInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function reset(): void
    {
        if ($this->entityManager->isOpen()) {
            $this->entityManager->clear();
        }
    }
}
