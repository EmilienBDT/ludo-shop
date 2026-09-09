<?php

declare(strict_types=1);

namespace App\Tests\Functional;

/**
 * Covers UC-17 / RG-28: public category navigation — list all categories,
 * view products by category, visibility rules, 404 on unknown slug.
 */
class CategoryTest extends FunctionalTestCase
{
    public function testCategoriesPageListsAll(): void
    {
        $this->client->request('GET', '/categories');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Stratégie');
        $this->assertSelectorTextContains('body', 'Famille');
        $this->assertSelectorTextContains('body', 'Ambiance');
        $this->assertSelectorTextContains('body', 'Coopératif');
    }

    public function testCategoryDetailShowsActiveProducts(): void
    {
        $this->client->request('GET', '/categories/strategie');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Stratégie');
        $this->assertSelectorTextContains('body', 'Catan');
        $this->assertSelectorTextContains('body', '7 Wonders');
        $this->assertSelectorTextContains('body', 'Wingspan');
        $this->assertSelectorTextContains('body', 'Pandemic');
        $this->assertSelectorTextContains('body', 'Azul');
    }

    public function testCategoryDetailExcludesInactiveProduct(): void
    {
        $product = $this->repository(\App\Entity\Product::class)->findOneBy(['reference' => 'CAT-001']);
        $this->assertNotNull($product);
        $product->setIsActive(false);
        $this->entityManager()->flush();

        $this->client->request('GET', '/categories/strategie');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Catan');
    }

    public function testCategoryDetail404ForUnknownSlug(): void
    {
        $this->client->request('GET', '/categories/unknown-slug');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCategoryDetailHidesMatureForMinor(): void
    {
        $this->login('minor@example.com');

        $this->client->request('GET', '/categories/ambiance');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Limite Limite');
    }

    public function testCategoryDetailShowsMatureForAdult(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/categories/ambiance');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Limite Limite');
    }

    public function testMultipleCategoriesPerProduct(): void
    {
        $this->client->request('GET', '/categories/famille');
        $this->assertSelectorTextContains('body', 'Dixit');

        $this->client->request('GET', '/categories/ambiance');
        $this->assertSelectorTextContains('body', 'Dixit');
    }
}
