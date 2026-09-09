<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;

/**
 * Covers UC-19 / RG-30: promotions with price strikethrough,
 * validation (promo < normal, dates not inverted), and period checks.
 */
class PromotionTest extends FunctionalTestCase
{
    private Product $catan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catan = $this->repository(Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($this->catan);
    }

    public function testAdminCanSetPromotion(): void
    {
        $this->login('admin@example.com');

        $crawler = $this->client->request('GET', '/admin/products/'.$this->catan->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $token = $crawler->filter('input[name="product_form[_token]"]')->attr('value');

        $this->client->request('POST', '/admin/products/'.$this->catan->getId().'/edit', [
            'product_form' => [
                'name' => $this->catan->getName(),
                'reference' => $this->catan->getReference(),
                'price' => '42.90',
                'description' => $this->catan->getDescription(),
                'stock' => 12,
                'promoPrice' => '29.99',
                'promoStartsAt' => '2026-08-01T00:00',
                'promoEndsAt' => '2026-12-31T23:59',
                '_token' => $token,
            ],
        ]);

        $this->client->followRedirect();
        $this->assertSelectorTextContains('body', 'Produit mis à jour.');
    }

    public function testPromoPriceShownOnProductPage(): void
    {
        $this->setPromotion($this->catan, '29.99', '2026-01-01', '2026-12-31');

        $this->client->request('GET', '/products/'.$this->catan->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '29,99');
        $this->assertSelectorTextContains('body', '42,90');
        $this->assertSelectorTextContains('body', 'Promo');
    }

    public function testNormalPriceShownWhenOutsidePromotionPeriod(): void
    {
        $this->setPromotion($this->catan, '29.99', '2025-01-01', '2025-12-31');

        $this->client->request('GET', '/products/'.$this->catan->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Promo');
        $this->assertSelectorTextContains('body', '42,90');
    }

    public function testPromoBadgeShownInCatalog(): void
    {
        $this->setPromotion($this->catan, '29.99', '2026-01-01', '2026-12-31');

        $this->client->request('GET', '/products');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', '29,99');
    }

    public function testPromoPriceRejectedWhenGreaterOrEqualToNormal(): void
    {
        $this->login('admin@example.com');

        $crawler = $this->client->request('GET', '/admin/products/'.$this->catan->getId().'/edit');
        $token = $crawler->filter('input[name="product_form[_token]"]')->attr('value');

        $this->client->request('POST', '/admin/products/'.$this->catan->getId().'/edit', [
            'product_form' => [
                'name' => $this->catan->getName(),
                'reference' => $this->catan->getReference(),
                'price' => '42.90',
                'description' => $this->catan->getDescription(),
                'stock' => 12,
                'promoPrice' => '50.00',
                'promoStartsAt' => '2026-08-01T00:00',
                'promoEndsAt' => '2026-12-31T23:59',
                '_token' => $token,
            ],
        ]);

        $this->assertSelectorTextContains('body', 'strictement inférieur');
    }

    public function testInvertedDatesRejected(): void
    {
        $this->login('admin@example.com');

        $crawler = $this->client->request('GET', '/admin/products/'.$this->catan->getId().'/edit');
        $token = $crawler->filter('input[name="product_form[_token]"]')->attr('value');

        $this->client->request('POST', '/admin/products/'.$this->catan->getId().'/edit', [
            'product_form' => [
                'name' => $this->catan->getName(),
                'reference' => $this->catan->getReference(),
                'price' => '42.90',
                'description' => $this->catan->getDescription(),
                'stock' => 12,
                'promoPrice' => '29.99',
                'promoStartsAt' => '2026-12-01T00:00',
                'promoEndsAt' => '2026-08-01T00:00',
                '_token' => $token,
            ],
        ]);

        $this->assertSelectorTextContains('body', 'postérieure');
    }

    private function setPromotion(Product $product, string $promoPrice, string $startsAt, string $endsAt): void
    {
        $product->setPromoPrice($promoPrice);
        $product->setPromoStartsAt(new \DateTimeImmutable($startsAt));
        $product->setPromoEndsAt(new \DateTimeImmutable($endsAt));
        $this->entityManager()->flush();
    }
}
