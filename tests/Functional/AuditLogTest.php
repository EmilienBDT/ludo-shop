<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\AuditLog;
use App\Service\AuditLogService;

/**
 * Covers RG-47 / UC-36: sensitive actions are logged with user, action and
 * timestamp in an append-only journal.
 */
class AuditLogTest extends FunctionalTestCase
{
    public function testAuditLogIsAppendOnly(): void
    {
        $user = $this->findUser('client@example.com');
        $service = $this->client->getContainer()->get(AuditLogService::class);

        $service->log($user, 'order.paid', ['order' => 'ABC123']);
        $service->log($user, 'order.paid', ['order' => 'DEF456']);

        $logs = $this->repository(AuditLog::class)->findAll();
        $this->assertCount(2, $logs);
    }

    public function testAuditLogEntryContainsUserActionAndTimestamp(): void
    {
        $user = $this->findUser('client@example.com');
        $service = $this->client->getContainer()->get(AuditLogService::class);

        $service->log($user, 'order.paid', ['order' => 'ABC123']);

        $log = $this->repository(AuditLog::class)->findOneBy(['action' => 'order.paid']);
        $this->assertNotNull($log);
        $this->assertSame('client@example.com', $log->getUser()->getEmail());
        $this->assertNotNull($log->getCreatedAt());
        $this->assertSame(['order' => 'ABC123'], $log->getDetails());
    }

    public function testAuditLogEntryIsImmutable(): void
    {
        $user = $this->findUser('client@example.com');
        $service = $this->client->getContainer()->get(AuditLogService::class);
        $service->log($user, 'order.paid', []);

        $log = $this->repository(AuditLog::class)->findOneBy(['action' => 'order.paid']);

        $this->assertFalse(method_exists($log, 'setAction'), 'No setter on action');
        $this->assertFalse(method_exists($log, 'setCreatedAt'), 'No setter on createdAt');
        $this->assertFalse(method_exists($log, 'setDetails'), 'No setter on details');
    }

    public function testSensitiveActionsAreLogged(): void
    {
        $csrfToken = $this->fetchLoginCsrfToken();

        $this->client->request('POST', '/login', [
            '_username' => 'client@example.com',
            '_password' => 'wrong-password',
            '_csrf_token' => $csrfToken,
        ]);

        $actions = array_map(
            static fn (AuditLog $log): string => $log->getAction(),
            $this->repository(AuditLog::class)->findAll(),
        );

        $this->assertContains('auth.login_failed', $actions);
    }

    public function testAdminCanViewAuditJournal(): void
    {
        $this->login('admin@example.com');

        $this->client->request('GET', '/admin/audit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Journal d\'audit');
    }

    public function testNonAdminIsDeniedFromAuditJournal(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/admin/audit');

        $this->assertResponseStatusCodeSame(403);
    }

    private function fetchLoginCsrfToken(): string
    {
        $crawler = $this->client->request('GET', '/login');

        return $crawler->filter('input[name="_csrf_token"]')->attr('value');
    }
}
