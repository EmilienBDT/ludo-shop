<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;

/**
 * UC-26 / RG-37: order confirmation email sent after payment with frozen data, one email per paid order.
 */
class OrderConfirmationEmailTest extends FunctionalTestCase
{
    public function testConfirmationEmailIsSentAfterPayment(): void
    {
        $order = $this->createPaidOrder();

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Paiement simulé accepté');
    }

    public function testConfirmationEmailContainsOrderNumber(): void
    {
        $order = $this->createPaidOrder();

        $messages = $this->getSentMessages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString($order->getNumber(), $messages[0]->getSubject());
    }

    public function testOnlyOneEmailIsSentPerPayment(): void
    {
        $this->createPaidOrder();

        $messages = $this->getSentMessages();
        $this->assertCount(1, $messages);
    }

    public function testNoEmailIsSentBeforePayment(): void
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

        $messages = $this->getSentMessages();
        $this->assertCount(0, $messages);
    }

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

    /** @return \Symfony\Component\Mime\RawMessage[] */
    private function getSentMessages(): array
    {
        $profile = $this->client->getProfile();
        $this->assertNotNull($profile, 'Le profiler n\'est pas disponible.');

        $collector = $profile->getCollector('mailer');

        return $collector->getEvents()->getMessages();
    }
}
