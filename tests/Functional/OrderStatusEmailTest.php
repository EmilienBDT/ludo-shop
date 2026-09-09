<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;

/**
 * UC-27 / RG-38: status change emails sent for each status reached, never on pending.
 */
class OrderStatusEmailTest extends FunctionalTestCase
{
    public function testStatusEmailIsSentOnShipped(): void
    {
        $order = $this->createPaidOrder();
        $this->loginAsAdmin();
        $this->changeStatus($order, 'shipped');

        $messages = $this->getSentMessages();
        $found = false;
        foreach ($messages as $message) {
            if (str_contains($message->getSubject(), 'Expédiée')) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Aucun email de statut "Expédiée" trouvé.');
    }

    public function testNoEmailIsSentOnPending(): void
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

    public function testStatusEmailSubjectContainsOrderNumber(): void
    {
        $order = $this->createPaidOrder();
        $this->loginAsAdmin();
        $this->changeStatus($order, 'shipped');

        $messages = $this->getSentMessages();
        $found = false;
        foreach ($messages as $message) {
            if (str_contains($message->getSubject(), $order->getNumber())) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Le numéro de commande n\'apparaît dans aucun sujet d\'email.');
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

    private function loginAsAdmin(): void
    {
        $this->login('admin@example.com');
    }

    private function changeStatus(Order $order, string $status): void
    {
        $crawler = $this->client->request('GET', '/admin/orders/'.$order->getId());
        $form = $crawler->filter('form[action*="status"]');
        $this->assertGreaterThan(0, $form->count(), 'Le formulaire de changement de statut est introuvable.');

        $this->client->submit($form->form(), [
            'order_status_form[status]' => $status,
        ]);

        $this->assertResponseRedirects();
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
