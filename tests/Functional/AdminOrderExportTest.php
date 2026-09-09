<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;

/**
 * D-02 — RG-41 / UC-30: CSV order export.
 */
class AdminOrderExportTest extends FunctionalTestCase
{
    private function createPaidOrder(): Order
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);

        $cartService = $this->client->getContainer()->get(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addProduct($cart, $product, 1);

        $crawler = $this->client->request('GET', '/checkout');
        $token = $crawler->filter('input[name="checkout_form[_token]"]')->attr('value');

        $this->client->request('POST', '/checkout', [
            'checkout_form' => [
                'addressLine' => '123 Rue Test',
                'postalCode' => '75001',
                'city' => 'Paris',
                'country' => 'FR',
                '_token' => $token,
            ],
        ]);

        $order = $this->repository(Order::class)->findOneBy(['user' => $user]);
        $this->client->request('POST', '/orders/'.$order->getId().'/pay');
        $this->entityManager()->clear();

        return $this->repository(Order::class)->find($order->getId());
    }

    public function testExportCsvDownload(): void
    {
        $this->createPaidOrder();
        $this->login('admin@example.com');

        $this->client->request('POST', '/admin/orders/export');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function testExportCsvContentContainsOrder(): void
    {
        $order = $this->createPaidOrder();
        $this->login('admin@example.com');

        $this->client->request('POST', '/admin/orders/export');

        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString($order->getNumber(), $content);
        $this->assertStringContainsString('Numéro', $content);
    }

    public function testExportAdminOnly(): void
    {
        $this->login('client@example.com');
        $this->client->request('POST', '/admin/orders/export');

        $this->assertResponseStatusCodeSame(403);
    }
}
