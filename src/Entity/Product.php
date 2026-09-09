<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[UniqueEntity(fields: ['reference'], message: 'Cette référence existe déjà.')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    private string $reference = '';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private string $price = '0.00';

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $stock = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $publisher = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(options: ['default' => 0])]
    private bool $isMature = false;

    #[ORM\Column(nullable: true)]
    private ?int $playtimeMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $setupMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $minAge = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxAge = null;

    #[ORM\Column(nullable: true)]
    private ?int $minPlayers = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxPlayers = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $promoPrice = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $promoStartsAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $promoEndsAt = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'products')]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getPriceFloat(): float
    {
        return (float) $this->price;
    }

    public function setPrice(float|string $price): static
    {
        $this->price = number_format((float) $price, 2, '.', '');

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = max(0, $stock);

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->isActive && $this->stock > 0;
    }

    public function isMature(): bool
    {
        return $this->isMature;
    }

    public function setIsMature(bool $isMature): static
    {
        $this->isMature = $isMature;

        return $this;
    }

    public function getPlaytimeMinutes(): ?int
    {
        return $this->playtimeMinutes;
    }

    public function setPlaytimeMinutes(?int $playtimeMinutes): static
    {
        $this->playtimeMinutes = $playtimeMinutes;

        return $this;
    }

    public function getSetupMinutes(): ?int
    {
        return $this->setupMinutes;
    }

    public function setSetupMinutes(?int $setupMinutes): static
    {
        $this->setupMinutes = $setupMinutes;

        return $this;
    }

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): static
    {
        $this->minAge = $minAge;

        return $this;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(?int $maxAge): static
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    public function getMinPlayers(): ?int
    {
        return $this->minPlayers;
    }

    public function setMinPlayers(?int $minPlayers): static
    {
        $this->minPlayers = $minPlayers;

        return $this;
    }

    public function getMaxPlayers(): ?int
    {
        return $this->maxPlayers;
    }

    public function setMaxPlayers(?int $maxPlayers): static
    {
        $this->maxPlayers = $maxPlayers;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function setPublisher(?string $publisher): static
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addProduct($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeProduct($this);
        }

        return $this;
    }

    public function getPromoPrice(): ?string
    {
        return $this->promoPrice;
    }

    public function getPromoPriceFloat(): ?float
    {
        return null !== $this->promoPrice ? (float) $this->promoPrice : null;
    }

    public function setPromoPrice(float|string|null $promoPrice): static
    {
        $this->promoPrice = null !== $promoPrice ? number_format((float) $promoPrice, 2, '.', '') : null;

        return $this;
    }

    public function getPromoStartsAt(): ?\DateTimeImmutable
    {
        return $this->promoStartsAt;
    }

    public function setPromoStartsAt(?\DateTimeImmutable $promoStartsAt): static
    {
        $this->promoStartsAt = $promoStartsAt;

        return $this;
    }

    public function getPromoEndsAt(): ?\DateTimeImmutable
    {
        return $this->promoEndsAt;
    }

    public function setPromoEndsAt(?\DateTimeImmutable $promoEndsAt): static
    {
        $this->promoEndsAt = $promoEndsAt;

        return $this;
    }
}
