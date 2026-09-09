<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;

class DashboardService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly int $lowStockThreshold = 5,
    ) {
    }

    /**
     * @return array{totalRevenue: float, orderCount: int, topProducts: list<array{name: string, quantity: int, revenue: float}>, lowStockProducts: list<array{name: string, stock: int}>}
     */
    public function getStats(): array
    {
        $em = $this->entityManager;

        $totalRevenue = (float) $em->createQueryBuilder()
            ->select('COALESCE(SUM(o.totalAmount), 0)')
            ->from('App\Entity\Order', 'o')
            ->where('o.status IN (:paidStatuses)')
            ->setParameter('paidStatuses', [
                OrderStatus::Paid->value,
                OrderStatus::Shipped->value,
                OrderStatus::Delivered->value,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        $orderCount = (int) $em->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from('App\Entity\Order', 'o')
            ->where('o.status IN (:paidStatuses)')
            ->setParameter('paidStatuses', [
                OrderStatus::Paid->value,
                OrderStatus::Shipped->value,
                OrderStatus::Delivered->value,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        $topProducts = $em->createQueryBuilder()
            ->select('p.name AS productName', 'SUM(oi.quantity) AS totalQuantity', 'SUM(oi.quantity * oi.unitPrice) AS totalRevenue')
            ->from('App\Entity\OrderItem', 'oi')
            ->innerJoin('oi.order', 'o')
            ->innerJoin('oi.product', 'p')
            ->where('o.status IN (:paidStatuses)')
            ->setParameter('paidStatuses', [
                OrderStatus::Paid->value,
                OrderStatus::Shipped->value,
                OrderStatus::Delivered->value,
            ])
            ->groupBy('p.id')
            ->orderBy('totalQuantity', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $lowStockProducts = $em->createQueryBuilder()
            ->select('p.name AS productName', 'p.stock')
            ->from('App\Entity\Product', 'p')
            ->where('p.isActive = true')
            ->andWhere('p.stock <= :threshold')
            ->setParameter('threshold', $this->lowStockThreshold)
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();

        return [
            'totalRevenue' => round($totalRevenue, 2),
            'orderCount' => $orderCount,
            'topProducts' => array_values(array_map(fn (array $row) => [
                'name' => (string) $row['productName'],
                'quantity' => (int) $row['totalQuantity'],
                'revenue' => round((float) $row['totalRevenue'], 2),
            ], $topProducts)),
            'lowStockProducts' => array_values(array_map(fn (array $row) => [
                'name' => (string) $row['productName'],
                'stock' => (int) $row['stock'],
            ], $lowStockProducts)),
        ];
    }
}
