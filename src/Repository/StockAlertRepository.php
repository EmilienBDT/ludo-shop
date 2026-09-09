<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product;
use App\Entity\StockAlert;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockAlert>
 */
class StockAlertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockAlert::class);
    }

    /** @return list<StockAlert> */
    public function findPendingByProduct(Product $product): array
    {
        return $this->createQueryBuilder('sa')
            ->where('sa.product = :product')
            ->andWhere('sa.isSent = false')
            ->setParameter('product', $product)
            ->getQuery()
            ->getResult();
    }

    /** @return list<StockAlert> */
    public function findPendingForInStockProducts(): array
    {
        return $this->createQueryBuilder('sa')
            ->innerJoin('sa.product', 'p')
            ->where('sa.isSent = false')
            ->andWhere('p.stock > 0')
            ->getQuery()
            ->getResult();
    }

    public function hasPendingAlert(User $user, Product $product): bool
    {
        return (int) $this->createQueryBuilder('sa')
            ->select('COUNT(sa.id)')
            ->where('sa.user = :user')
            ->andWhere('sa.product = :product')
            ->andWhere('sa.isSent = false')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
