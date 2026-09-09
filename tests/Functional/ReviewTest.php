<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Enum\OrderStatus;

/**
 * Covers UC-18 / RG-29: reviews are only allowed after a paid order,
 * one review per (product, user), average rating is displayed.
 */
class ReviewTest extends FunctionalTestCase
{
    private Product $catan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catan = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($this->catan);
    }

    public function testReviewFormVisibleForEligibleUser(): void
    {
        $this->createPaidOrderWithProduct('client@example.com', $this->catan);
        $this->login('client@example.com');

        $this->client->request('GET', '/products/'.$this->catan->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Laisser un avis');
    }

    public function testReviewFormHiddenForNonBuyer(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/products/'.$this->catan->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Laisser un avis');
    }

    public function testReviewCanBeSubmittedAfterPaidOrder(): void
    {
        $this->createPaidOrderWithProduct('client@example.com', $this->catan);
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/reviews', [
            'review' => ['rating' => 5, 'comment' => 'Excellent jeu de stratégie !'],
        ]);

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Excellent jeu de stratégie !');
        $this->assertSelectorTextContains('body', '5/5');
    }

    public function testDuplicateReviewIsRejected(): void
    {
        $this->createPaidOrderWithProduct('client@example.com', $this->catan);
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/reviews', [
            'review' => ['rating' => 5, 'comment' => 'Premier avis'],
        ]);
        $this->client->followRedirect();

        $this->client->request('POST', '/products/'.$this->catan->getId().'/reviews', [
            'review' => ['rating' => 4, 'comment' => 'Deuxième avis'],
        ]);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Premier avis');
        $this->assertSelectorTextNotContains('body', 'Deuxième avis');
    }

    public function testReviewWithoutPurchaseIsRejected(): void
    {
        $this->login('client@example.com');

        $this->client->request('POST', '/products/'.$this->catan->getId().'/reviews', [
            'review' => ['rating' => 5, 'comment' => 'Je n\'ai pas acheté ce jeu'],
        ]);

        $this->client->followRedirect();
        $this->assertSelectorTextNotContains('body', 'Je n\'ai pas acheté ce jeu');
    }

    public function testAverageRatingIsDisplayed(): void
    {
        $this->createPaidOrderWithProduct('client@example.com', $this->catan);
        $this->login('client@example.com');
        $this->client->request('POST', '/products/'.$this->catan->getId().'/reviews', [
            'review' => ['rating' => 4, 'comment' => ''],
        ]);
        $this->client->followRedirect();

        $catan = $this->repository(Product::class)->find($this->catan->getId());
        $this->createPaidOrderWithProduct('admin@example.com', $catan);
        $this->login('admin@example.com');
        $this->client->request('POST', '/products/'.$catan->getId().'/reviews', [
            'review' => ['rating' => 2, 'comment' => ''],
        ]);
        $this->client->followRedirect();

        $this->client->request('GET', '/products/'.$catan->getId());
        $this->assertSelectorTextContains('body', '3,0 / 5');
    }

    private function createPaidOrderWithProduct(string $email, Product $product): void
    {
        $user = $this->findUser($email);

        $order = new Order(
            number: 'ORD-TEST-'.bin2hex(random_bytes(4)),
            user: $user,
            addressLine: '1 rue Test',
            city: 'Paris',
            postalCode: '75000',
            country: 'FR',
        );
        $order->setStatus(OrderStatus::Paid);
        $order->setPaidAt(new \DateTimeImmutable());

        $item = new OrderItem($order, $product, $product->getName(), $product->getPrice(), 1);
        $order->addItem($item);

        $this->entityManager()->persist($order);
        $this->entityManager()->flush();
    }
}
