<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Service\CartService;

/**
 * B-04 — RG-36 / UC-25: invoice export (printable view).
 */
class InvoiceExportTest extends FunctionalTestCase
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

    public function testPrintPageShowsFrozenAmounts(): void
    {
        $order = $this->createPaidOrder();
        $invoice = $order->getInvoice();
        $this->assertNotNull($invoice);

        $this->login('client@example.com');
        $this->client->request('GET', '/orders/'.$order->getId().'/invoice/print');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $invoice->getNumber());
        $this->assertSelectorTextContains('body', number_format((float) $invoice->getTotalTtc(), 2, ',', ' ').' €');
    }

    public function testPrintRouteExists(): void
    {
        $order = $this->createPaidOrder();
        $invoice = $order->getInvoice();
        $this->assertNotNull($invoice);

        $this->login('client@example.com');
        $crawler = $this->client->request('GET', '/orders/'.$order->getId());
        $this->assertSelectorTextContains('a[href*="invoice/print"]', 'Imprimer la facture');
    }

    public function testOtherUserCannotPrintInvoice(): void
    {
        $order = $this->createPaidOrder();
        $orderId = $order->getId();

        $this->login('minor@example.com');
        $this->client->request('GET', '/orders/'.$orderId.'/invoice/print');

        $this->assertResponseStatusCodeSame(403);
    }
}
