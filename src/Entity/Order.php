<?php

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[UniqueEntity(fields: ['number'], message: 'Ce numéro de commande existe déjà.')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $number;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(length: 255)]
    private string $addressLine;

    #[ORM\Column(length: 100)]
    private string $city;

    #[ORM\Column(length: 20)]
    private string $postalCode;

    #[ORM\Column(length: 100)]
    private string $country;

    #[ORM\Column(enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::Pending;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $totalAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2)]
    private string $vatRate = '0.00';

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, OrderItem> */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\OneToOne(mappedBy: 'order', cascade: ['persist'])]
    private ?Invoice $invoice = null;

    /** @var Collection<int, OrderStatusHistory> */
    #[ORM\OneToMany(targetEntity: OrderStatusHistory::class, mappedBy: 'order', orphanRemoval: true)]
    private Collection $statusHistory;

    public function __construct(
        string $number,
        User $user,
        string $addressLine,
        string $city,
        string $postalCode,
        string $country,
    ) {
        $this->number = $number;
        $this->user = $user;
        $this->addressLine = $addressLine;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->items = new ArrayCollection();
        $this->statusHistory = new ArrayCollection();
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAddressLine(): string
    {
        return $this->addressLine;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function getTotalAmountFloat(): float
    {
        return (float) $this->totalAmount;
    }

    public function setTotalAmount(float|string $totalAmount): static
    {
        $this->totalAmount = number_format((float) $totalAmount, 2, '.', '');

        return $this;
    }

    public function getVatRate(): string
    {
        return $this->vatRate;
    }

    public function getVatRateFloat(): float
    {
        return (float) $this->vatRate;
    }

    public function setVatRate(float|string $vatRate): static
    {
        $this->vatRate = number_format((float) $vatRate, 2, '.', '');

        return $this;
    }

    public function getTotalHt(): float
    {
        return round($this->getTotalAmountFloat() / (1 + $this->getVatRateFloat() / 100), 2);
    }

    public function getVatAmount(): float
    {
        return round($this->getTotalAmountFloat() - $this->getTotalHt(), 2);
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, OrderItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function isPaid(): bool
    {
        return OrderStatus::Paid === $this->status;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    /** @return Collection<int, OrderStatusHistory> */
    public function getStatusHistory(): Collection
    {
        return $this->statusHistory;
    }
}
