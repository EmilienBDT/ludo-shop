<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;

/**
 * Covers UC-16 / RG-27 through the HTTP endpoint: search by name and combined
 * filters, active-only and maturity-aware results.
 */
class CatalogSearchTest extends FunctionalTestCase
{
    public function testSearchByQuery(): void
    {
        $this->client->request('GET', '/products/search', ['q' => 'Catan']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');
    }

    public function testSearchByPublisher(): void
    {
        $this->client->request('GET', '/products/search', ['publisher' => 'Kosmos']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');
        $this->assertSelectorTextNotContains('body', 'Dixit');
    }

    public function testCombinedCriteriaInAnd(): void
    {
        $this->client->request('GET', '/products/search', ['q' => 'Catan', 'publisher' => 'Kosmos', 'min_players' => 3]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');

        $this->client->request('GET', '/products/search', ['q' => 'Catan', 'publisher' => 'Mattel']);

        $this->assertSelectorTextContains('body', 'Aucun produit disponible');
    }

    public function testInactiveProductIsExcluded(): void
    {
        $inactive = new Product();
        $inactive->setName('Archéologie perdue');
        $inactive->setReference('ARC-998');
        $inactive->setPrice(10.00);
        $inactive->setIsActive(false);
        $this->entityManager()->persist($inactive);
        $this->entityManager()->flush();

        $this->client->request('GET', '/products/search', ['q' => 'Archéologie']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Aucun produit disponible');
    }

    public function testMatureProductHiddenForMinor(): void
    {
        $this->login('minor@example.com');

        $this->client->request('GET', '/products/search', ['q' => 'Limite']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Limite Limite');
        $this->assertSelectorTextContains('body', 'Aucun produit disponible');
    }

    public function testMatureProductShownForAdult(): void
    {
        $this->login('client@example.com');

        $this->client->request('GET', '/products/search', ['q' => 'Limite']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Limite Limite');
    }

    public function testSearchWithoutCriteriaReturnsCatalogue(): void
    {
        $this->client->request('GET', '/products/search');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Catan');
        $this->assertSelectorTextContains('body', 'Dixit');
    }

    public function testSearchIsPaginatedAndPreservesCriteria(): void
    {
        $this->client->request('GET', '/products/search', ['min_players' => 2, 'page' => 2]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.pagination');
        $this->assertSelectorExists('a.page-link[href*="min_players=2"]');
    }

    public function testUnknownQueryShowsEmptyMessage(): void
    {
        $this->client->request('GET', '/products/search', ['q' => 'jeu-inexistant-xyz']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Aucun produit disponible');
    }
}
