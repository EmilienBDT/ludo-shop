<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function hasUserReviewedProduct(User $user, Product $product): bool
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :user')
            ->andWhere('r.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function averageRating(Product $product): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->where('r.product = :product')
            ->andWhere('r.isHidden = false')
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();

        return null === $result ? null : round((float) $result, 1);
    }

    /** @return list<Review> */
    public function findVisibleByProduct(Product $product): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.product = :product')
            ->andWhere('r.isHidden = false')
            ->setParameter('product', $product)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
