<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\OrderStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_status_history')]
class OrderStatusHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'statusHistory')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column(enumType: OrderStatus::class)]
    private OrderStatus $status;

    #[ORM\ManyToOne]
    private ?User $changedBy = null;

    #[ORM\Column]
    private \DateTimeImmutable $changedAt;

    public function __construct(
        Order $order,
        OrderStatus $status,
        ?User $changedBy,
    ) {
        $this->order = $order;
        $this->status = $status;
        $this->changedBy = $changedBy;
        $this->changedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getChangedBy(): ?User
    {
        return $this->changedBy;
    }

    public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }
}
