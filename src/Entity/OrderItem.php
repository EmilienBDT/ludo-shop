<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\ManyToOne]
    private ?Product $product = null;

    #[ORM\Column(length: 150)]
    private string $productName;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $unitPrice = '0.00';

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantity = 1;

    public function __construct(
        Order $order,
        ?Product $product,
        string $productName,
        float|string $unitPrice,
        int $quantity,
    ) {
        $this->order = $order;
        $this->product = $product;
        $this->productName = $productName;
        $this->setUnitPrice($unitPrice);
        $this->setQuantity($quantity);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function getUnitPriceFloat(): float
    {
        return (float) $this->unitPrice;
    }

    public function setUnitPrice(float|string $unitPrice): static
    {
        $this->unitPrice = number_format((float) $unitPrice, 2, '.', '');

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = max(1, $quantity);

        return $this;
    }

    public function getLineTotal(): float
    {
        return round($this->getUnitPriceFloat() * $this->quantity, 2);
    }
}
