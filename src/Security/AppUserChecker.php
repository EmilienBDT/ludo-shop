<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AppUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isDeleted()) {
            throw new CustomUserMessageAccountStatusException('Ce compte a été supprimé.');
        }

        if (null !== $user->getLockedUntil() && $user->getLockedUntil() > new \DateTimeImmutable()) {
            throw new CustomUserMessageAccountStatusException('Ce compte est verrouillé pour cause de trop nombreuses tentatives de connexion. Réessayez dans 15 minutes.');
        }

        if (!$user->isEmailVerified()) {
            throw new CustomUserMessageAccountStatusException('Veuillez confirmer votre adresse e-mail avant de vous connecter.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
