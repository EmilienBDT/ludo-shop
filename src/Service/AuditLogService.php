<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AuditLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Append-only journal: only appends entries (RG-47). No method removes or
 * updates an existing entry.
 */
class AuditLogService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @param array<string, mixed> $details */
    public function log(User $user, string $action, array $details = []): AuditLog
    {
        $entry = new AuditLog($user, $action, $details);
        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $entry;
    }
}
