<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Command\ProcessStockAlertsCommand;
use App\Entity\Product;
use App\Entity\StockAlert;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Covers UC-21 / RG-32: stock alert subscription, single-use email,
 * only on out-of-stock products, auto-processing on admin stock edit,
 * and console command.
 */
class StockAlertTest extends FunctionalTestCase
{
    private Product $catan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catan = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($this->catan);
    }

    public function testSubscribeToStockAlertOnOOSProduct(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('GET', '/products/'.$this->catan->getId());
        $this->assertSelectorTextContains('body', "M'avertir au retour en stock");

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'notifié');
    }

    public function testSubscribeRejectedWhenProductInStock(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'actuellement en stock');
    }

    public function testDuplicateSubscribeRejected(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'déjà une alerte');
    }

    public function testAlertShowsOnProductPage(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->client->request('GET', '/products/'.$this->catan->getId());
        $this->assertSelectorTextContains('body', 'Alerte active');
    }

    public function testStockAlertButtonRedirectsBackToWishlist(): void
    {
        $this->setStock($this->catan, 0);
        $this->login('client@example.com');

        $this->client->request('POST', '/wishlist/add/'.$this->catan->getId());
        $this->client->followRedirect();

        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert', server: [
            'HTTP_REFERER' => 'http://localhost/wishlist',
        ]);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'Ma liste de souhaits');
    }

    public function testAutoProcessOnAdminStockUpdate(): void
    {
        $this->setStock($this->catan, 0);

        $this->login('client@example.com');
        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $alert = $this->repository(StockAlert::class)->findOneBy([
            'user' => $this->findUser('client@example.com'),
            'product' => $this->catan,
        ]);
        $this->assertNotNull($alert);
        $this->assertFalse($alert->isSent());

        $this->login('admin@example.com');
        $crawler = $this->client->request('GET', '/admin/products/'.$this->catan->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $token = $crawler->filter('input[name="product_form[_token]"]')->attr('value');

        $this->client->request('POST', '/admin/products/'.$this->catan->getId().'/edit', [
            'product_form' => [
                'name' => $this->catan->getName(),
                'reference' => $this->catan->getReference(),
                'publisher' => $this->catan->getPublisher(),
                'price' => $this->catan->getPrice(),
                'description' => $this->catan->getDescription(),
                'stock' => 5,
                'isActive' => true,
                '_token' => $token,
            ],
        ]);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('body', 'Produit mis à jour');

        $this->entityManager()->clear();
        $sentAlert = $this->repository(StockAlert::class)->find($alert->getId());
        $this->assertTrue($sentAlert->isSent());
    }

    public function testProcessStockAlertsCommand(): void
    {
        $this->setStock($this->catan, 0);

        $this->login('client@example.com');
        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $this->entityManager()->clear();
        $product = $this->repository(Product::class)->find($this->catan->getId());
        $this->assertNotNull($product);
        $product->setStock(10);
        $this->entityManager()->flush();

        $command = $this->client->getContainer()->get(ProcessStockAlertsCommand::class);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('1 stock alert(s) sent.', $tester->getDisplay());

        $this->entityManager()->clear();
        $alert = $this->repository(StockAlert::class)->findOneBy([
            'user' => $this->findUser('client@example.com'),
            'product' => $this->catan,
        ]);
        $this->assertNotNull($alert);
        $this->assertTrue($alert->isSent());
    }

    public function testProcessCommandSendsNoEmailWhenNoPendingAlerts(): void
    {
        $command = $this->client->getContainer()->get(ProcessStockAlertsCommand::class);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('0 stock alert(s) sent.', $tester->getDisplay());
    }

    public function testProcessCommandDoesNotSendForStillOOSProducts(): void
    {
        $this->setStock($this->catan, 0);

        $this->login('client@example.com');
        $this->client->request('POST', '/products/'.$this->catan->getId().'/stock-alert');
        $this->client->followRedirect();

        $command = $this->client->getContainer()->get(ProcessStockAlertsCommand::class);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertStringContainsString('0 stock alert(s) sent.', $tester->getDisplay());

        $this->entityManager()->clear();
        $alert = $this->repository(StockAlert::class)->findOneBy([
            'user' => $this->findUser('client@example.com'),
            'product' => $this->catan,
        ]);
        $this->assertNotNull($alert);
        $this->assertFalse($alert->isSent());
    }

    private function setStock(Product $product, int $stock): void
    {
        $product->setStock($stock);
        $this->entityManager()->flush();
    }
}
