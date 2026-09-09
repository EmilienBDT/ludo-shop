<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /** @return list<Product> */
    public function findActive(bool $includeMature): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->orderBy('p.createdAt', 'ASC');

        if (!$includeMature) {
            $qb->andWhere('p.isMature = false');
        }

        return $qb->getQuery()->getResult();
    }

    /** @return list<Product> */
    public function findActivePaginated(int $limit, int $offset, bool $includeMature): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->orderBy('p.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!$includeMature) {
            $qb->andWhere('p.isMature = false');
        }

        return $qb->getQuery()->getResult();
    }

    public function countActive(bool $includeMature): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.isActive = true');

        if (!$includeMature) {
            $qb->andWhere('p.isMature = false');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Searches the catalogue (RG-27). Only active products are returned, and
     * mature products are hidden when $includeMature is false (RG-24). All
     * non-empty criteria are combined with AND.
     *
     * @param array{
     *     q?: string|null,
     *     publisher?: string|null,
     *     price_min?: int|float|string|null,
     *     price_max?: int|float|string|null,
     *     min_age?: int|null,
     *     max_age?: int|null,
     *     min_players?: int|null,
     *     max_players?: int|null,
     *     max_playtime?: int|null,
     * } $criteria
     *
     * @return list<Product>
     */
    public function search(array $criteria, bool $includeMature, int $limit, int $offset): array
    {
        return $this->buildSearchQuery($criteria, $includeMature)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /** @param array<string, mixed> $criteria */
    public function countSearch(array $criteria, bool $includeMature): int
    {
        $qb = $this->buildSearchQuery($criteria, $includeMature)
            ->select('COUNT(p.id)')
            ->resetDQLPart('orderBy');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /** @param array<string, mixed> $criteria */
    private function buildSearchQuery(array $criteria, bool $includeMature): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->orderBy('p.name', 'ASC');

        if (!$includeMature) {
            $qb->andWhere('p.isMature = false');
        }

        if (isset($criteria['q']) && '' !== trim((string) $criteria['q'])) {
            $qb->andWhere('LOWER(p.name) LIKE :q')
                ->setParameter('q', '%'.mb_strtolower(trim((string) $criteria['q'])).'%');
        }

        if (isset($criteria['publisher']) && '' !== trim((string) $criteria['publisher'])) {
            $qb->andWhere('p.publisher = :publisher')
                ->setParameter('publisher', trim((string) $criteria['publisher']));
        }

        if (isset($criteria['price_min']) && '' !== (string) $criteria['price_min']) {
            $qb->andWhere('p.price >= :price_min')
                ->setParameter('price_min', (string) $criteria['price_min']);
        }

        if (isset($criteria['price_max']) && '' !== (string) $criteria['price_max']) {
            $qb->andWhere('p.price <= :price_max')
                ->setParameter('price_max', (string) $criteria['price_max']);
        }

        if (isset($criteria['min_age'])) {
            $qb->andWhere('p.minAge >= :min_age')
                ->setParameter('min_age', (int) $criteria['min_age']);
        }

        if (isset($criteria['max_age'])) {
            $qb->andWhere('p.minAge <= :max_age')
                ->setParameter('max_age', (int) $criteria['max_age']);
        }

        if (isset($criteria['min_players'])) {
            $qb->andWhere('p.minPlayers >= :min_players')
                ->setParameter('min_players', (int) $criteria['min_players']);
        }

        if (isset($criteria['max_players'])) {
            $qb->andWhere('p.maxPlayers <= :max_players')
                ->setParameter('max_players', (int) $criteria['max_players']);
        }

        if (isset($criteria['max_playtime'])) {
            $qb->andWhere('p.playtimeMinutes <= :max_playtime')
                ->setParameter('max_playtime', (int) $criteria['max_playtime']);
        }

        return $qb;
    }

    /** @return list<Product> */
    public function findActiveByCategory(Category $category, bool $includeMature): array
    {
        return $this->buildCategoryQuery($category, $includeMature)
            ->getQuery()
            ->getResult();
    }

    public function countActiveByCategory(Category $category, bool $includeMature): int
    {
        $qb = $this->buildCategoryQuery($category, $includeMature)
            ->select('COUNT(p.id)')
            ->resetDQLPart('orderBy');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function buildCategoryQuery(Category $category, bool $includeMature): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.categories', 'c')
            ->where('c = :category')
            ->andWhere('p.isActive = true')
            ->orderBy('p.name', 'ASC')
            ->setParameter('category', $category);

        if (!$includeMature) {
            $qb->andWhere('p.isMature = false');
        }

        return $qb;
    }
}
