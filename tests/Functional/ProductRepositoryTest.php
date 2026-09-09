<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Product;
use App\Repository\ProductRepository;

/**
 * Covers RG-27 / UC-16: each search criterion filters, criteria combine in AND,
 * only active products are returned and maturity visibility is respected.
 */
class ProductRepositoryTest extends FunctionalTestCase
{
    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->repository(Product::class);
    }

    public function testSearchByQueryMatchesNameCaseInsensitively(): void
    {
        $products = $this->repository->search(['q' => 'catan'], false, 50, 0);

        $this->assertCount(1, $products);
        $this->assertSame('Catan', $products[0]->getName());
    }

    public function testSearchByQueryMatchesPartialName(): void
    {
        $products = $this->repository->search(['q' => 'Code'], false, 50, 0);

        $this->assertCount(1, $products);
        $this->assertSame('Code Names', $products[0]->getName());
    }

    public function testSearchByPublisherIsExactMatch(): void
    {
        $products = $this->repository->search(['publisher' => 'Kosmos'], false, 50, 0);

        $this->assertCount(1, $products);
        $this->assertSame('Catan', $products[0]->getName());

        $products = $this->repository->search(['publisher' => 'kosmos'], false, 50, 0);
        $this->assertSame([], $products);
    }

    public function testSearchByPriceRange(): void
    {
        $products = $this->repository->search(['price_min' => 30, 'price_max' => 40], false, 50, 0);

        $names = array_map(static fn (Product $p): string => $p->getName(), $products);
        sort($names);
        $this->assertSame(['7 Wonders', 'Azul', 'Carcassonne'], $names);
    }

    public function testSearchByMinAge(): void
    {
        $products = $this->repository->search(['min_age' => 14], false, 50, 0);

        $names = array_map(static fn (Product $p): string => $p->getName(), $products);
        $this->assertSame(['Code Names'], $names);
    }

    public function testSearchByMinPlayers(): void
    {
        $products = $this->repository->search(['min_players' => 3], false, 50, 0);

        $names = array_map(static fn (Product $p): string => $p->getName(), $products);
        sort($names);
        $this->assertSame(['7 Wonders', 'Catan', 'Dixit'], $names);
    }

    public function testSearchByMaxPlayers(): void
    {
        $products = $this->repository->search(['max_players' => 4], false, 50, 0);

        $names = array_map(static fn (Product $p): string => $p->getName(), $products);
        sort($names);
        $this->assertSame(['Azul', 'Catan', 'Kingdomino', 'Pandemic'], $names);
    }

    public function testSearchByMaxPlaytime(): void
    {
        $products = $this->repository->search(['max_playtime' => 20], false, 50, 0);

        $names = array_map(static fn (Product $p): string => $p->getName(), $products);
        sort($names);
        $this->assertSame(['Code Names', 'Exploding Kittens', 'Jungle Speed', 'Kingdomino', 'Uno'], $names);
    }

    public function testCriteriaCombineInAnd(): void
    {
        $products = $this->repository->search(
            ['q' => 'Catan', 'publisher' => 'Kosmos', 'min_players' => 3],
            false,
            50,
            0,
        );

        $this->assertCount(1, $products);
        $this->assertSame('Catan', $products[0]->getName());

        $products = $this->repository->search(['q' => 'Catan', 'publisher' => 'Mattel'], false, 50, 0);
        $this->assertSame([], $products);
    }

    public function testInactiveProductsAreExcluded(): void
    {
        $inactive = new Product();
        $inactive->setName('Archéologie perdue');
        $inactive->setReference('ARC-999');
        $inactive->setPrice(10.00);
        $inactive->setIsActive(false);
        $this->entityManager()->persist($inactive);
        $this->entityManager()->flush();

        $products = $this->repository->search(['q' => 'Archéologie'], false, 50, 0);

        $this->assertSame([], $products);
    }

    public function testMatureProductsVisibility(): void
    {
        $hidden = $this->repository->search(['q' => 'Limite'], false, 50, 0);
        $this->assertSame([], $hidden);

        $shown = $this->repository->search(['q' => 'Limite'], true, 50, 0);
        $this->assertCount(1, $shown);
        $this->assertSame('Limite Limite', $shown[0]->getName());
    }

    public function testPaginationIsApplied(): void
    {
        $firstPage = $this->repository->search([], false, 2, 0);
        $secondPage = $this->repository->search([], false, 2, 2);

        $this->assertCount(2, $firstPage);
        $this->assertCount(2, $secondPage);
        $this->assertNotSame($firstPage[0]->getId(), $secondPage[0]->getId());
    }

    public function testCountSearchMatchesResults(): void
    {
        $products = $this->repository->search(['min_players' => 3], false, 50, 0);
        $total = $this->repository->countSearch(['min_players' => 3], false);

        $this->assertCount($total, $products);
    }

    public function testEmptyCriteriaReturnAllVisibleProducts(): void
    {
        $products = $this->repository->search([], false, 50, 0);

        $this->assertCount(12, $products);
    }
}
