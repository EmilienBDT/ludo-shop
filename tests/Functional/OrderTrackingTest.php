<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\OrderStatusHistory;
use App\Entity\Product;
use App\Service\CartService;

/**
 * B-03 — RG-35 / UC-24: order status tracking with timestamps.
 */
class OrderTrackingTest extends FunctionalTestCase
{
    public function testPaymentCreatesHistoryEntry(): void
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

        $this->assertResponseRedirects();
        $order = $this->repository(Order::class)->findOneBy(['user' => $user]);
        $this->assertNotNull($order);
        $this->assertSame('pending', $order->getStatus()->value);

        $this->client->request('POST', '/orders/'.$order->getId().'/pay');

        $this->assertResponseRedirects();
        $this->entityManager()->clear();

        $historyEntries = $this->repository(OrderStatusHistory::class)->findBy(
            ['order' => $this->repository(Order::class)->find($order->getId())],
            ['changedAt' => 'ASC'],
        );

        $this->assertCount(1, $historyEntries);
        $this->assertSame('paid', $historyEntries[0]->getStatus()->value);
        $this->assertNotNull($historyEntries[0]->getChangedAt());
    }

    public function testTrackingIsReadOnlyForClient(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
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
        $crawler = $this->client->request('GET', '/orders/'.$order->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Commande');
        $this->assertSelectorNotExists('form[action*="status"]');
    }

    public function testOtherUserCannotSeeTracking(): void
    {
        $this->login('client@example.com');
        $user = $this->findUser('client@example.com');

        $product = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
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
        $orderId = $order->getId();

        $this->login('admin@example.com');
        $this->client->request('GET', '/orders/'.$orderId);

        $this->assertResponseStatusCodeSame(403);
    }
}
