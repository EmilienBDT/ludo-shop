<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StockAlertRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: StockAlertRepository::class)]
#[ORM\Table(name: 'stock_alerts')]
#[UniqueEntity(fields: ['user', 'product'], message: 'Vous avez déjà une alerte pour ce produit.')]
class StockAlert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column]
    private bool $isSent = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(User $user, Product $product)
    {
        $this->user = $user;
        $this->product = $product;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function isSent(): bool
    {
        return $this->isSent;
    }

    public function markSent(): static
    {
        $this->isSent = true;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
