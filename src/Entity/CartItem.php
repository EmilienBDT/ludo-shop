<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'cart_items')]
#[ORM\UniqueConstraint(name: 'uniq_cart_product', columns: ['cart_id', 'product_id'])]
#[UniqueEntity(fields: ['cart', 'product'], message: 'Ce produit est déjà dans votre panier.')]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private Cart $cart;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column]
    #[Assert\Positive]
    private int $quantity = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $unitPrice = '0.00';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(Product $product)
    {
        $this->product = $product;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): static
    {
        $this->cart = $cart;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getLineTotal(): float
    {
        return round($this->getUnitPriceFloat() * $this->quantity, 2);
    }
}
