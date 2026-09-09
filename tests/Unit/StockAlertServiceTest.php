<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Entity\StockAlert;
use App\Entity\User;
use App\Repository\StockAlertRepository;
use App\Service\StockAlertService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;

class StockAlertServiceTest extends TestCase
{
    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        $this->user = (new User())->setEmail('test@example.com');
        $this->product = (new Product())->setName('Catan')->setPrice(50.00)->setStock(0);
    }

    public function testRequestPersistsAlert(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $repo = $this->createMock(StockAlertRepository::class);
        $repo->method('hasPendingAlert')->willReturn(false);
        $mailer = $this->createMock(MailerInterface::class);

        $service = new StockAlertService($em, $repo, $mailer);
        $service->request($this->user, $this->product);
    }

    public function testRequestThrowsWhenProductInStock(): void
    {
        $this->product->setStock(5);
        $repo = $this->createMock(StockAlertRepository::class);
        $service = new StockAlertService($this->createMock(EntityManagerInterface::class), $repo, $this->createMock(MailerInterface::class));

        $this->expectException(\DomainException::class);
        $service->request($this->user, $this->product);
    }

    public function testRequestThrowsOnDuplicate(): void
    {
        $repo = $this->createMock(StockAlertRepository::class);
        $repo->method('hasPendingAlert')->willReturn(true);
        $service = new StockAlertService($this->createMock(EntityManagerInterface::class), $repo, $this->createMock(MailerInterface::class));

        $this->expectException(\DomainException::class);
        $service->request($this->user, $this->product);
    }

    public function testProcessSendsEmailsAndMarksSent(): void
    {
        $alert = new StockAlert($this->user, $this->product);
        $this->product->setStock(5);

        $repo = $this->createMock(StockAlertRepository::class);
        $repo->method('findPendingForInStockProducts')->willReturn([$alert]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send');

        $service = new StockAlertService($em, $repo, $mailer);
        $count = $service->process();

        $this->assertSame(1, $count);
        $this->assertTrue($alert->isSent());
    }

    public function testProcessReturnsZeroWhenNoPending(): void
    {
        $repo = $this->createMock(StockAlertRepository::class);
        $repo->method('findPendingForInStockProducts')->willReturn([]);

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $service = new StockAlertService(
            $this->createMock(EntityManagerInterface::class),
            $repo,
            $mailer,
        );

        $this->assertSame(0, $service->process());
    }

    public function testHasPendingAlertDelegatesToRepository(): void
    {
        $repo = $this->createMock(StockAlertRepository::class);
        $repo->method('hasPendingAlert')->willReturn(true);

        $service = new StockAlertService(
            $this->createMock(EntityManagerInterface::class),
            $repo,
            $this->createMock(MailerInterface::class),
        );

        $this->assertTrue($service->hasPendingAlert($this->user, $this->product));
    }
}
