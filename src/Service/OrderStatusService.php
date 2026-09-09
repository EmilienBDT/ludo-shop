<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderStatusHistory;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;

class OrderStatusService
{
    /** @var array<string, list<OrderStatus>> */
    private const ALLOWED_TRANSITIONS = [
        OrderStatus::Pending->value => [OrderStatus::Paid, OrderStatus::Cancelled],
        OrderStatus::Paid->value => [OrderStatus::Shipped],
        OrderStatus::Shipped->value => [OrderStatus::Delivered],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderMailerService $orderMailerService,
    ) {
    }

    /** @return list<OrderStatus> */
    public function allowedTransitions(Order $order): array
    {
        return self::ALLOWED_TRANSITIONS[$order->getStatus()->value] ?? [];
    }

    public function canTransition(Order $order, OrderStatus $target): bool
    {
        return in_array($target, self::ALLOWED_TRANSITIONS[$order->getStatus()->value] ?? [], true);
    }

    public function transition(Order $order, OrderStatus $target, ?User $changedBy = null): void
    {
        if (!$this->canTransition($order, $target)) {
            throw new \LogicException(sprintf('Transition %s -> %s interdite.', $order->getStatus()->value, $target->value));
        }

        $order->setStatus($target);

        $history = new OrderStatusHistory(
            order: $order,
            status: $target,
            changedBy: $changedBy,
        );
        $this->entityManager->persist($history);

        // RG-38: send status notification for each status reached, never on pending.
        // Paid is handled by the order confirmation email (RG-37).
        if (OrderStatus::Pending !== $target && OrderStatus::Paid !== $target) {
            $this->orderMailerService->sendStatusNotification($order);
        }
    }
}
