<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Service\LoginThrottler;

/**
 * Covers RG-45 / UC-34: after 5 failed logins the account is locked for 15
 * minutes; the counter resets after a successful login.
 */
class LoginThrottlingTest extends FunctionalTestCase
{
    public function testAccountIsLockedAfterFiveFailures(): void
    {
        $this->login('client@example.com');

        $user = $this->findUser('client@example.com');
        $throttler = $this->client->getContainer()->get(LoginThrottler::class);

        for ($i = 0; $i < 5; ++$i) {
            $throttler->recordFailure($user);
        }

        $this->assertTrue($throttler->isLocked($user), 'Account should be locked after 5 failures');
        $this->assertNotNull($user->getLockedUntil());
    }

    public function testLockExpiresAfterFifteenMinutes(): void
    {
        $user = $this->findUser('client@example.com');
        $throttler = $this->client->getContainer()->get(LoginThrottler::class);

        for ($i = 0; $i < 5; ++$i) {
            $throttler->recordFailure($user);
        }
        $this->assertTrue($throttler->isLocked($user));

        $user->setLockedUntil((new \DateTimeImmutable())->modify('-1 second'));
        $this->entityManager()->flush();

        $this->assertFalse($throttler->isLocked($user), 'Lock must expire after 15 minutes');
    }

    public function testCounterResetsAfterSuccessfulLogin(): void
    {
        $user = $this->findUser('client@example.com');
        $throttler = $this->client->getContainer()->get(LoginThrottler::class);

        $throttler->recordFailure($user);
        $throttler->recordFailure($user);

        $throttler->reset($user);

        $this->assertSame(0, $user->getFailedLoginAttempts(), 'Counter must reset after success');
        $this->assertNull($user->getLockedUntil());
        $this->assertFalse($throttler->isLocked($user));
    }

    public function testFailedLoginEndpointBlocksAfterFiveAttempts(): void
    {
        $csrfToken = $this->fetchLoginCsrfToken();

        for ($i = 0; $i < 5; ++$i) {
            $this->client->request('POST', '/login', [
                '_username' => 'client@example.com',
                '_password' => 'wrong-password',
                '_csrf_token' => $csrfToken,
            ]);
        }

        $user = $this->findUser('client@example.com');
        $this->assertGreaterThanOrEqual(5, $user->getFailedLoginAttempts());
        $this->assertNotNull($user->getLockedUntil(), 'Endpoint must lock the account after 5 failures');
    }

    private function fetchLoginCsrfToken(): string
    {
        $crawler = $this->client->request('GET', '/login');

        return $crawler->filter('input[name="_csrf_token"]')->attr('value');
    }
}
