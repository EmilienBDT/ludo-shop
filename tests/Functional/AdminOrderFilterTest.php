<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;

/**
 * D-03 — RG-42 / UC-31: admin order filters (combined AND).
 */
class AdminOrderFilterTest extends FunctionalTestCase
{
    private function createPaidOrder(string $clientEmail = 'client@example.com'): Order
    {
        $this->login($clientEmail);
        $user = $this->findUser($clientEmail);

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

    public function testFilterByStatus(): void
    {
        $this->createPaidOrder();
        $this->login('admin@example.com');

        $this->client->request('GET', '/admin/orders?status=paid');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'commande(s) trouvée(s)');
    }

    public function testFilterByClient(): void
    {
        $this->createPaidOrder();
        $this->login('admin@example.com');

        $this->client->request('GET', '/admin/orders?client=client');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Claire Client');
    }

    public function testFilterByNumber(): void
    {
        $order = $this->createPaidOrder();
        $this->login('admin@example.com');

        $this->client->request('GET', '/admin/orders?number='.$order->getNumber());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $order->getNumber());
    }

    public function testAdminAccessRequired(): void
    {
        $this->login('client@example.com');
        $this->client->request('GET', '/admin/orders');

        $this->assertResponseStatusCodeSame(403);
    }
}
