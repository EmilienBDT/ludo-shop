<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\AuditLogService;
use App\Service\LoginThrottler;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Tracks failed logins (RG-45) and resets the counter on success.
 * Sensitive login events are written to the audit journal (RG-47).
 */
class LoginThrottlingListener
{
    public function __construct(
        private readonly LoginThrottler $throttler,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    #[AsEventListener(event: LoginFailureEvent::class)]
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();
        if (null === $passport) {
            return;
        }

        $user = $passport->getUser();
        if (!$user instanceof User) {
            return;
        }

        $this->throttler->recordFailure($user);
        $this->auditLogService->log($user, 'auth.login_failed');
    }

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $this->throttler->reset($user);
        $this->auditLogService->log($user, 'auth.login_success');
    }
}
