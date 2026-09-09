<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $number;

    #[ORM\OneToOne(inversedBy: 'invoice')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $totalHt = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $totalTtc = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $vatRate = '0.00';

    #[ORM\Column]
    private \DateTimeImmutable $issuedAt;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $number,
        Order $order,
        User $user,
        float|string $totalHt,
        float|string $totalTtc,
        float|string $vatRate,
    ) {
        $this->number = $number;
        $this->order = $order;
        $this->user = $user;
        $this->totalHt = number_format((float) $totalHt, 2, '.', '');
        $this->totalTtc = number_format((float) $totalTtc, 2, '.', '');
        $this->vatRate = number_format((float) $vatRate, 2, '.', '');
        $this->issuedAt = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTotalHt(): string
    {
        return $this->totalHt;
    }

    public function getTotalHtFloat(): float
    {
        return (float) $this->totalHt;
    }

    public function getTotalTtc(): string
    {
        return $this->totalTtc;
    }

    public function getTotalTtcFloat(): float
    {
        return (float) $this->totalTtc;
    }

    public function getVatRate(): string
    {
        return $this->vatRate;
    }

    public function getVatRateFloat(): float
    {
        return (float) $this->vatRate;
    }

    public function getIssuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
