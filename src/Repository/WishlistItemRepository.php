<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\WishlistItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WishlistItem>
 */
class WishlistItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WishlistItem::class);
    }

    /** @return list<WishlistItem> */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('w')
            ->innerJoin('w.product', 'p')
            ->where('w.user = :user')
            ->andWhere('p.isActive = true')
            ->setParameter('user', $user)
            ->orderBy('w.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
