<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Category;
use App\Entity\Product;
use App\Tests\Functional\FunctionalTestCase;

/**
 * Covers RG-28: deleting a category must NOT delete associated products.
 */
class CategoryProductTest extends FunctionalTestCase
{
    public function testDeletingCategoryPreservesProducts(): void
    {
        $repo = $this->repository(Category::class);
        $category = $repo->findOneBy(['slug' => 'strategie']);
        $this->assertNotNull($category, 'Stratégie category must exist in fixtures.');

        $totalBefore = $this->repository(Product::class)->count([]);
        $productsInCategory = $category->getProducts()->count();

        $this->assertGreaterThan(0, $productsInCategory, 'Stratégie must have at least one product.');

        $this->entityManager()->remove($category);
        $this->entityManager()->flush();

        $totalAfter = $this->repository(Product::class)->count([]);

        $this->assertSame($totalBefore, $totalAfter, 'Products must survive category deletion (RG-28).');
    }
}
