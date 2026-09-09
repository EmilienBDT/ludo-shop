<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param array{status?: string|null, dateFrom?: string|null, dateTo?: string|null, client?: string|null, number?: string|null} $filters
     *
     * @return list<Order>
     */
    public function searchAdmin(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.user', 'u')->addSelect('u')
            ->orderBy('o.createdAt', 'DESC');

        if (!empty($filters['status']) && null !== OrderStatus::tryFrom($filters['status'])) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', OrderStatus::from($filters['status']));
        }

        if (!empty($filters['dateFrom'])) {
            $qb->andWhere('o.createdAt >= :dateFrom')
               ->setParameter('dateFrom', new \DateTimeImmutable($filters['dateFrom']));
        }

        if (!empty($filters['dateTo'])) {
            $qb->andWhere('o.createdAt <= :dateTo')
               ->setParameter('dateTo', (new \DateTimeImmutable($filters['dateTo']))->modify('+1 day'));
        }

        if (!empty($filters['client'])) {
            $qb->andWhere('LOWER(u.email) LIKE :client OR LOWER(u.firstName) LIKE :client OR LOWER(u.lastName) LIKE :client')
               ->setParameter('client', '%'.mb_strtolower($filters['client']).'%');
        }

        if (!empty($filters['number'])) {
            $qb->andWhere('LOWER(o.number) LIKE :number')
               ->setParameter('number', '%'.mb_strtolower($filters['number']).'%');
        }

        /* @var list<Order> */
        return $qb->getQuery()->getResult();
    }
}
