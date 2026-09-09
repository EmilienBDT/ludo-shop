<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Single-use email verification (RG-49): a token is generated at registration
 * and consumed on the first visit to the confirmation link.
 */
class EmailVerificationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function issueToken(User $user): void
    {
        $user->setEmailVerifyToken(bin2hex(random_bytes(32)));
        $this->entityManager->flush();
    }

    public function sendVerificationEmail(User $user, string $verificationUrl): void
    {
        $email = (new TemplatedEmail())
            ->from('no-reply@ludo-shop.local')
            ->to($user->getEmail())
            ->subject('Confirmez votre adresse e-mail')
            ->htmlTemplate('email/verify_email.html.twig')
            ->context([
                'user' => $user,
                'verificationUrl' => $verificationUrl,
            ]);

        $this->mailer->send($email);
    }

    public function findUserByToken(string $token): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['emailVerifyToken' => $token]);

        return $user && !$user->isDeleted() ? $user : null;
    }

    public function confirm(User $user): void
    {
        $user->setEmailVerifiedAt(new \DateTimeImmutable());
        $user->setEmailVerifyToken(null);
        $this->entityManager->flush();
    }
}
