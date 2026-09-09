<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\AuditLog;
use App\Entity\User;
use App\Service\AuditLogService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AuditLogServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;

    private AuditLogService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new AuditLogService($this->entityManager);
    }

    public function testLogAppendsEntry(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $entry = $this->service->log($user, 'order.paid', ['order' => 'ABC']);

        $this->assertInstanceOf(AuditLog::class, $entry);
        $this->assertSame('order.paid', $entry->getAction());
        $this->assertSame(['order' => 'ABC'], $entry->getDetails());
        $this->assertNotNull($entry->getCreatedAt());
    }

    public function testLogEntryCannotBeModifiedNorDeleted(): void
    {
        $this->assertFalse(method_exists(AuditLog::class, 'setAction'));
        $this->assertFalse(method_exists(AuditLog::class, 'setUser'));
        $this->assertFalse(method_exists(AuditLog::class, 'setDetails'));
        $this->assertFalse(method_exists(AuditLog::class, 'setCreatedAt'));
    }

    public function testServiceOffersNoUpdateOrDeleteMethod(): void
    {
        $this->assertFalse(method_exists(AuditLogService::class, 'delete'));
        $this->assertFalse(method_exists(AuditLogService::class, 'update'));
        $this->assertFalse(method_exists(AuditLogService::class, 'remove'));
    }
}
