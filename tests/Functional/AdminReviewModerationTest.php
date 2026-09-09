<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Review;
use App\Service\CartService;

/**
 * D-04 — RG-43 / UC-32: review moderation (hide/show).
 */
class AdminReviewModerationTest extends FunctionalTestCase
{
    private function createReview(): Review
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

        $this->client->request('POST', '/products/'.$product->getId().'/reviews', [
            'review' => [
                'rating' => 5,
                'comment' => 'Excellent jeu',
            ],
        ]);

        $this->entityManager()->clear();

        return $this->repository(Review::class)->findOneBy(['user' => $user, 'product' => $product]);
    }

    public function testAdminCanToggleReviewVisibility(): void
    {
        $review = $this->createReview();
        $this->login('admin@example.com');

        $this->client->request('POST', '/admin/reviews/'.$review->getId().'/toggle');

        $this->assertResponseRedirects();
        $this->entityManager()->clear();

        $toggled = $this->repository(Review::class)->find($review->getId());
        $this->assertTrue($toggled->isHidden());
    }

    public function testHiddenReviewNotVisibleOnProductPage(): void
    {
        $review = $this->createReview();
        $this->login('admin@example.com');
        $this->client->request('POST', '/admin/reviews/'.$review->getId().'/toggle');

        $this->client->request('GET', '/products/'.$review->getProduct()->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Excellent jeu');
    }

    public function testReviewStillExistsInDatabaseWhenHidden(): void
    {
        $review = $this->createReview();
        $this->login('admin@example.com');
        $this->client->request('POST', '/admin/reviews/'.$review->getId().'/toggle');

        $this->entityManager()->clear();
        $stillThere = $this->repository(Review::class)->find($review->getId());
        $this->assertNotNull($stillThere);
        $this->assertTrue($stillThere->isHidden());
    }

    public function testAdminAccessRequired(): void
    {
        $review = $this->createReview();
        $this->login('client@example.com');

        $this->client->request('POST', '/admin/reviews/'.$review->getId().'/toggle');

        $this->assertResponseStatusCodeSame(403);
    }
}
