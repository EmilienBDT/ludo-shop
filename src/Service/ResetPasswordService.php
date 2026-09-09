<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function requestReset(string $email): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'isDeleted' => false,
        ]);
        if (null === $user) {
            return null;
        }

        $user->setResetToken(bin2hex(random_bytes(32)));
        $user->setResetTokenExpiresAt((new \DateTimeImmutable())->modify('+2 hours'));
        $this->entityManager->flush();

        return $user;
    }

    public function sendResetEmail(User $user, string $resetUrl): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@ludo-shop.local')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('email/reset_password.html.twig')
            ->context([
                'user' => $user,
                'resetUrl' => $resetUrl,
            ]);

        $this->mailer->send($email);
    }

    public function findUserByToken(string $token): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);
        if (null === $user || $user->isDeleted()) {
            return null;
        }

        $expiresAt = $user->getResetTokenExpiresAt();
        if (null === $expiresAt || $expiresAt < new \DateTimeImmutable()) {
            return null;
        }

        return $user;
    }

    public function resetPassword(User $user, string $plainPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);
        $this->entityManager->flush();
    }
}
