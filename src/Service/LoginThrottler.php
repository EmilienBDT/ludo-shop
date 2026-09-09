<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Brute-force protection (RG-45): after 5 failed logins the account is locked
 * for 15 minutes; the counter resets after a successful login.
 */
class LoginThrottler
{
    public const MAX_ATTEMPTS = 5;

    public const LOCK_DURATION = '+15 minutes';

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function recordFailure(User $user): void
    {
        $user->setFailedLoginAttempts($user->getFailedLoginAttempts() + 1);

        if ($user->getFailedLoginAttempts() >= self::MAX_ATTEMPTS) {
            $user->setLockedUntil((new \DateTimeImmutable())->modify(self::LOCK_DURATION));
        }

        $this->entityManager->flush();
    }

    public function reset(User $user): void
    {
        $user->setFailedLoginAttempts(0);
        $user->setLockedUntil(null);
        $this->entityManager->flush();
    }

    public function isLocked(User $user): bool
    {
        $lockedUntil = $user->getLockedUntil();

        return null !== $lockedUntil && $lockedUntil > new \DateTimeImmutable();
    }
}
